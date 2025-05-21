<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Abortable;
use App\Console\Commands\Traits\AbortException;
use App\Console\Commands\Traits\ExceptionLevel;
use App\Models\SiteSubmission;
use App\Models\Submission;
use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
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
                        $this->abort("Duplicate organisation name within CSV: " . $row['name']);
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
            $header = Str::snake(trim($header, "\xEF\xBB\xBF"));
            if (Str::startsWith($header, 'hq_street')) {
                // Str::snake doesn't add an underscore before numbers, so hack in a quick fix for hqStreet1 and hqStreet2
                $header = Str::replace('hq_street', 'hq_street_', $header);
            }
            $this->headerOrder[] = $header;
        }

        $this->assert(in_array('name', $this->headerOrder), "No name column found");
        $this->assert(in_array('type', $this->headerOrder), "No type column found");
        $this->assert(in_array('hq_street_1', $this->headerOrder), "No hqStreet1 column found");
        $this->assert(in_array('hq_street_2', $this->headerOrder), "No hqStreet1 column found");
        $this->assert(in_array('hq_city', $this->headerOrder), "No hqCity column found");
        $this->assert(in_array('hq_state', $this->headerOrder), "No hqState column found");
        $this->assert(in_array('hq_zipcode', $this->headerOrder), "No hqZipcode column found");
        $this->assert(in_array('hq_country', $this->headerOrder), "No hqCountry column found");
        $this->assert(count($this->headerOrder) == 8, "Invalid number of columns found: " . json_encode($this->headerOrder));
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

        $this->assert(!empty($row['name']), "No name found: " . json_encode($row));
        $this->assert(!empty($row['type']), "No type found: " . json_encode($row));
        $this->assert(!empty($row['hq_street_1']), "No hqStreet1 found: " . json_encode($row));
        // We allow hq_street_2 to be empty
        $this->assert(!empty($row['hq_city']), "No hqCity found: " . json_encode($row));
        $this->assert(!empty($row['hq_state']), "No hqState found: " . json_encode($row));
        $this->assert(!empty($row['hq_zipcode']), "No hqZipcode found: " . json_encode($row));
        $this->assert(!empty($row['hq_country']), "No hqCountry found: " . json_encode($row));
        $this->assert(!Organisation::where('name', $row['name'])->exists(), "Organisation already exists: " . $row['name']);

        return $row;
    }

    protected function createOrganisation($orgData): Organisation
    {
        return Organisation::create(array_merge($orgData, [
            'status' => Organisation::STATUS_DRAFT,
            // These two are required in the DB schema, but seem unused; all values in the DB are the same
            'private' => false,
            'currency' => 'USD',
            // The script does not create test orgs
            'is_test' => false
        ]));
    }
}
