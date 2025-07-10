<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Abortable;
use App\Console\Commands\Traits\AbortException;
use App\Models\Submission;
use App\Models\V2\Forms\Application;
use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\FundingProgramme;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class BulkOrganisationImport extends Command
{
    use Abortable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bulk-organisation-import {file} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports organisation data from a .csv';

    protected array $headerOrder = [];

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
                    if ($rows->contains(fn ($existing) => $existing['name'] == $row['name'])) {
                        $this->abort('Duplicate organisation name within CSV: ' . $row['name']);
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
                $orgs = [];
                foreach ($rows as $orgData) {
                    $org = $this->createOrganisation($orgData);
                    $orgs[] = [$org->uuid, $org->name];
                }

                Excel::store(new class ($orgs) implements WithHeadings, FromArray {
                    use Exportable;

                    private mixed $orgs;

                    public function __construct($orgs)
                    {
                        $this->orgs = $orgs;
                    }

                    public function headings(): array
                    {
                        return ['uuid', 'name'];
                    }

                    public function array(): array
                    {
                        return $this->orgs;
                    }
                }, 'organisations.csv', 'local');

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
            $header = trim($header, "\xEF\xBB\xBF");
            // Str::snake doesn't add an underscore before numbers, so add a regex search to handle that case
            $header = Str::snake(Str::replaceMatches('/[0-9]+/', fn ($matches) => "_$matches[0]", $header));
            $this->headerOrder[] = $header;
        }

        $this->assert(in_array('name', $this->headerOrder), 'No name column found');
        $this->assert(in_array('type', $this->headerOrder), 'No type column found');
        $this->assert(in_array('hq_street_1', $this->headerOrder), 'No hqStreet1 column found');
        $this->assert(in_array('hq_street_2', $this->headerOrder), 'No hqStreet2 column found');
        $this->assert(in_array('hq_city', $this->headerOrder), 'No hqCity column found');
        $this->assert(in_array('hq_state', $this->headerOrder), 'No hqState column found');
        $this->assert(in_array('hq_zipcode', $this->headerOrder), 'No hqZipcode column found');
        $this->assert(in_array('hq_country', $this->headerOrder), 'No hqCountry column found');
        $this->assert(in_array('phone', $this->headerOrder), 'No phone column found');
        $this->assert(in_array('countries', $this->headerOrder), 'No countries column found');
        $this->assert(in_array('funding_programme_uuid', $this->headerOrder), 'No fundingProgrammeUuid column found');
        $this->assert(in_array('currency', $this->headerOrder), 'No currency column found');
        $this->assert(in_array('level_0_proposed', $this->headerOrder), 'No level0Proposed column found');
        $this->assert(in_array('level_1_proposed', $this->headerOrder), 'No level1Proposed column found');
        $this->assert(in_array('level_0_past_restoration', $this->headerOrder), 'No level0PastRestoration column found');
        $this->assert(in_array('level_1_past_restoration', $this->headerOrder), 'No level1PastRestoration column found');
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
            if (! empty($field)) {
                $row[$field] = $cell;
            }
        }

        if (empty($row)) {
            return null;
        }

        // hq_street_2, hq_zipcode, currency, level_0_proposed, level_1_proposed, level_0_past_restoration,
        // and level_1_past_restoration are not required
        $this->assert(! empty($row['name']), 'No name found: ' . json_encode($row));
        $this->assert(! empty($row['type']), 'No type found: ' . json_encode($row));
        $this->assert(! empty($row['hq_street_1']), 'No hqStreet1 found: ' . json_encode($row));
        $this->assert(! empty($row['hq_city']), 'No hqCity found: ' . json_encode($row));
        $this->assert(! empty($row['hq_state']), 'No hqState found: ' . json_encode($row));
        $this->assert(! empty($row['hq_country']), 'No hqCountry found: ' . json_encode($row));
        $this->assert(! empty($row['phone']), 'No phone found: ' . json_encode($row));
        $this->assert(! empty($row['countries']), 'No countries found: ' . json_encode($row));
        $this->assert(! empty($row['funding_programme_uuid']), 'No fundingProgrammeUuid found: ' . json_encode($row));

        // Default to USD, since all current rows in the DB use this value.
        $row['currency'] = empty($row['currency']) ? 'USD' : $row['currency'];

        $row['countries'] = $this->decodeArray($row, 'countries');
        $row['level_0_proposed'] = $this->decodeArray($row, 'level_0_proposed');
        $row['level_1_proposed'] = $this->decodeArray($row, 'level_1_proposed');
        $row['level_0_past_restoration'] = $this->decodeArray($row, 'level_0_past_restoration');
        $row['level_1_past_restoration'] = $this->decodeArray($row, 'level_1_past_restoration');

        $this->assert(! Organisation::where('name', $row['name'])->exists(), 'Organisation already exists: ' . $row['name']);
        $this->assert(FundingProgramme::isUuid($row['funding_programme_uuid'])->exists(), 'Funding programme not found: ' . $row['funding_programme_uuid']);
        $this->assert(
            FundingProgramme::isUuid($row['funding_programme_uuid'])->first()->stages()->count() > 0,
            'Funding programme has no stages: ' . $row['funding_programme_uuid']
        );

        return $row;
    }

    /**
     * @throws AbortException
     */
    protected function decodeArray(array $row, string $field): array
    {
        $value = $row[$field];
        if (empty($value)) {
            return [];
        }

        $values = explode('|', $value);
        $this->assert(! collect($values)->contains(fn ($val) => empty($val)), "Invalid $field entry found: " . json_encode($row));

        return $values;
    }

    protected function createOrganisation($orgData): Organisation
    {
        $org = Organisation::create(array_merge($orgData, [
            'status' => Organisation::STATUS_PENDING,
            // This one is required in the DB schema, but seems unused; all values in the DB are the same
            'private' => false,
            // The script does not create test orgs
            'is_test' => false,
        ]));

        // Create a blank application for the indicated funding programme. This code follows the pattern from
        // StoreFormSubmissionController.
        // Note: the application updated_by and form submission user_id must be left blank because we don't have a
        // user in this context. The user import script that will be run after this one will add that data when the
        // first user is added to the org.
        $fundingProgramme = FundingProgramme::isUuid($orgData['funding_programme_uuid'])->first();
        $projectPitch = ProjectPitch::create([
            'organisation_id' => $org->uuid,
            'funding_programme_id' => $fundingProgramme->uuid,
            'level_0_proposed' => $orgData['level_0_proposed'],
            'level_1_proposed' => $orgData['level_1_proposed'],
        ]);
        $application = Application::create([
            'organisation_uuid' => $org->uuid,
            'funding_programme_uuid' => $fundingProgramme->uuid,
        ]);
        FormSubmission::create([
            'form_id' => $fundingProgramme->stages()->first()->form->uuid,
            'stage_uuid' => $fundingProgramme->stages()->first()->uuid,
            'organisation_uuid' => $org->uuid,
            'project_pitch_uuid' => $projectPitch->uuid,
            'application_id' => $application->id,
            'status' => FormSubmission::STATUS_STARTED,
            'answers' => [],
        ]);

        return $org;
    }
}
