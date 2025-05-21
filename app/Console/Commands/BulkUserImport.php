<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Abortable;
use App\Console\Commands\Traits\AbortException;
use App\Mail\BulkUserCreation;
use App\Models\PasswordReset;
use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class BulkUserImport extends Command
{
    use Abortable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bulk-user-import {file} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports user data from a .csv';

    protected array $headerOrder = [];

    protected const VALID_LOCALES = ['en-US', 'es-MX', 'pt-BR', 'fr-FR'];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->executeAbortableScript(function () {
            $fileHandle = fopen($this->argument('file'), 'r');
            $this->parseHeaders(fgetcsv($fileHandle));

            $rows = collect();
            $parseErrors = [];
            while ($csvRow = fgetcsv($fileHandle)) {
                try {
                    $row = $this->parseRow($csvRow);
                    if ($rows->contains(fn ($existing) => $existing['email_address'] == $row['email_address'])) {
                        $this->abort('Duplicate user emails within CSV: ' . $row['email_address']);
                    }

                    $rows->push($row);
                } catch (AbortException $e) {
                    $parseErrors[] = $e;
                }
            }

            if (! empty($parseErrors)) {
                $this->warn("Errors encountered during parsing CSV Rows:\n");
                foreach ($parseErrors as $error) {
                    $this->logException($error);
                }

                $this->error("Processing aborted\n");
                exit(1);
            }

            $rows = $rows->filter();
            fclose($fileHandle);

            if ($this->option('dry-run')) {
                $this->info(json_encode($rows->values(), JSON_PRETTY_PRINT) . "\n\n");
            } else {
                // A separate loop so we can validate the input before we start creating any orgs
                foreach ($rows as $userData) {
                    $this->createUser($userData);
                }

                $this->info("Organisation import complete! Organisation export saved in organisations.csv\n\n");
            }
        });
    }

    /**
     * @throws AbortException
     */
    protected function parseHeaders($headerRow): void
    {
        foreach ($headerRow as $header) {
            // Excel puts some garbage at the beginning of the file that we need to filter out.
            $this->headerOrder[] = Str::snake(trim($header, "\xEF\xBB\xBF"));
        }

        $this->assert(in_array('organisation_uuid', $this->headerOrder), 'No organisationUuid column found');
        $this->assert(in_array('first_name', $this->headerOrder), 'No firstName column found');
        $this->assert(in_array('last_name', $this->headerOrder), 'No lastName column found');
        $this->assert(in_array('email_address', $this->headerOrder), 'No emailAddress column found');
        $this->assert(in_array('role', $this->headerOrder), 'No role column found');
        $this->assert(in_array('locale', $this->headerOrder), 'No locale column found');
        $this->assert(count($this->headerOrder) == 6, 'Invalid number of columns found: ' . json_encode($this->headerOrder));
    }

    /**
     * @throws AbortException
     */
    protected function parseRow($csvRow): ?array
    {
        $row = [];
        foreach ($csvRow as $index => $cell) {
            if ($index >= count($this->headerOrder)) {
                continue;
            }

            $field = $this->headerOrder[$index];
            $row[$field] = $cell;
        }

        if (empty($row)) {
            return null;
        }

        $this->assert(! empty($row['organisation_uuid']), 'No organisationUuid found: ' . json_encode($row));
        $this->assert(! empty($row['first_name']), 'No firstName found: ' . json_encode($row));
        $this->assert(! empty($row['last_name']), 'No lastName found: ' . json_encode($row));
        $this->assert(! empty($row['email_address']), 'No email found: ' . json_encode($row));
        $this->assert(! empty($row['role']), 'No role found: ' . json_encode($row));
        $this->assert(! empty($row['locale']), 'No locale found: ' . json_encode($row));

        $this->assert(! User::where('email_address', $row['email_address'])->exists(), 'User already exists: ' . $row['email_address']);
        $this->assert(Organisation::isUuid($row['organisation_uuid'])->exists(), 'Organisation not found: ' . $row['organisation_uuid']);
        $this->assert(Role::where('name', $row['role'])->exists(), 'Role not found: ' . $row['role']);
        $this->assert(in_array($row['locale'], self::VALID_LOCALES), 'Invalid locale: ' . $row['locale']);

        return $row;
    }

    protected function createUser($userData): void
    {
        $org = Organisation::isUuid($userData['organisation_uuid'])->first();
        $user = User::create(array_merge($userData, ['organisation_id' => $org->id]));
        $user->syncRoles([$userData['role']]);

        // If the org hasn't yet had a user attached to its applications and form submissions, attach this user to them.
        foreach ($org->applications as $application) {
            if (empty($application->updated_by)) {
                $application->update(['updated_by' => $user->id]);
            }

            foreach ($application->formSubmissions as $formSubmission) {
                if ($formSubmission->user_id == null) {
                    $formSubmission->update(['user_id' => $user->id]);
                }
            }
        }

        $passwordReset = PasswordReset::create([
            'user_id' => $user->id,
            'token' => Str::random(32),
        ]);
        $fundingProgrammeName = $org->applications()->first()?->fundingProgramme?->name;
        Mail::to($user->email_address)->send(new BulkUserCreation($passwordReset->token, $fundingProgrammeName, $user));
    }
}
