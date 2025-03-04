<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Abortable;
use App\Console\Commands\Traits\AbortException;
use App\Console\Commands\Traits\ExceptionLevel;
use App\Models\SiteSubmission;
use App\Models\Submission;
use App\Models\V2\Demographics\Demographic;
use App\Models\V2\Demographics\DemographicCollections;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BulkWorkdayImport extends Command
{
    use Abortable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bulk-workday-import {type} {file} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports workday data from a .csv';

    protected const COLLECTIONS = [
        'sites' => [
            'Paid_site_establishment' => DemographicCollections::PAID_SITE_ESTABLISHMENT,
            'Vol_site_establishment' => DemographicCollections::VOLUNTEER_SITE_ESTABLISHMENT,
            'Paid_planting' => DemographicCollections::PAID_PLANTING,
            'Vol_planting' => DemographicCollections::VOLUNTEER_PLANTING,
            'Paid_monitoring' => DemographicCollections::PAID_SITE_MONITORING,
            'Vol_monitoring' => DemographicCollections::VOLUNTEER_SITE_MONITORING,
            'Paid_maintenance' => DemographicCollections::PAID_SITE_MAINTENANCE,
            'Vol_maintenance' => DemographicCollections::VOLUNTEER_SITE_MAINTENANCE,
            'Paid_other' => DemographicCollections::PAID_OTHER,
            'Vol_other' => DemographicCollections::VOLUNTEER_OTHER,
        ],

        'projects' => [
            'Paid_project_management' => DemographicCollections::PAID_PROJECT_MANAGEMENT,
            'Vol_project_management' => DemographicCollections::VOLUNTEER_PROJECT_MANAGEMENT,
            'Paid_nursery_ops' => DemographicCollections::PAID_NURSERY_OPERATIONS,
            'Vol_nursery_ops' => DemographicCollections::VOLUNTEER_NURSERY_OPERATIONS,
            'Paid_seed_collection' => DemographicCollections::PAID_NURSERY_OPERATIONS,
            'Vol_seed_collection' => DemographicCollections::VOLUNTEER_NURSERY_OPERATIONS,
            'Paid_other' => DemographicCollections::PAID_OTHER,
            'Vol_other' => DemographicCollections::VOLUNTEER_OTHER,
        ],
    ];

    protected const MODEL_CONFIGS = [
        'sites' => [
            'id' => 'site_id',
            'submission_id' => 'site_submission_id',
            'model' => SiteReport::class,
            'old_model' => SiteSubmission::class,
            'parent' => 'site',
        ],

        'projects' => [
            'id' => 'project_id',
            'submission_id' => 'programme_submission_id',
            'model' => ProjectReport::class,
            'old_model' => Submission::class,
            'parent' => 'project',
        ],
    ];

    protected const DEMOGRAPHICS = [
        'women' => ['type' => 'gender', 'subtype' => 'female', 'name' => null],
        'men' => ['type' => 'gender', 'subtype' => 'male', 'name' => null],
        'non-binary' => ['type' => 'gender', 'subtype' => 'non-binary', 'name' => null],
        'nonbinary' => ['type' => 'gender', 'subtype' => 'non-binary', 'name' => null],
        'gender-unknown' => ['type' => 'gender', 'subtype' => 'unknown', 'name' => null],
        'no-gender' => ['type' => 'gender', 'subtype' => 'unknown', 'name' => null],

        'youth_15-24' => ['type' => 'age', 'subtype' => 'youth', 'name' => null],
        'adult_24-64' => ['type' => 'age', 'subtype' => 'adult', 'name' => null],
        'elder_65+' => ['type' => 'age', 'subtype' => 'elder', 'name' => null],
        'age-unknown' => ['type' => 'age', 'subtype' => 'unknown', 'name' => null],
        'age_unknown' => ['type' => 'age', 'subtype' => 'unknown', 'name' => null],

        'indigenous' => ['type' => 'ethnicity', 'subtype' => 'indigenous', 'name' => null],
        'ethnicity-other' => ['type' => 'ethnicity', 'subtype' => 'other', 'name' => null],
        'ethnicity-unknown' => ['type' => 'ethnicity', 'subtype' => 'unknown', 'name' => null],
    ];

    protected array $modelConfig;

    protected Collection $collections;

    protected array $columns = [];

    protected array $indices = [];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->executeAbortableScript(function () {
            $type = $this->argument('type');

            $this->assert(! empty(self::COLLECTIONS[$type]), "Unknown type: $type");
            $this->modelConfig = self::MODEL_CONFIGS[$type];
            $this->collections = collect(self::COLLECTIONS[$type]);

            $fileHandle = fopen($this->argument('file'), 'r');
            $this->parseHeaders(fgetcsv($fileHandle));

            $rows = collect();
            $parseErrors = [];
            while ($csvRow = fgetcsv($fileHandle)) {
                try {
                    $rows->push($this->parseRow($csvRow, $parseErrors));
                } catch (AbortException $e) {
                    $parseErrors[] = $e;
                }
            }

            if (! empty($parseErrors)) {
                $this->warn("Errors and warnings encountered during parsing CSV Rows:\n");
                foreach ($parseErrors as $error) {
                    $this->logException($error);
                }

                $shouldAbort = ! empty(collect($parseErrors)->first(fn ($e) => $e->level == ExceptionLevel::Error));
                if ($shouldAbort) {
                    $this->error("Parsing aborted\n");
                    exit(1);
                }
            }

            $rows = $rows->filter();
            fclose($fileHandle);

            if ($this->option('dry-run')) {
                $this->info(json_encode($rows->values(), JSON_PRETTY_PRINT) . "\n\n");
            } else {
                // A separate loop so we can validate as much input as possible before we start persisting any records
                foreach ($rows as $reportData) {
                    $report = $this->modelConfig['model']::isUuid($reportData['report_uuid'])->first();
                    $this->persistWorkdays($report, $reportData);
                }

                $this->info("Workday import complete!\n\n");
            }
        });
    }

    /**
     * @throws AbortException
     */
    protected function parseHeaders($headerRow): void
    {
        $idHeader = $this->modelConfig['id'];
        $submissionIdHeader = $this->modelConfig['submission_id'];

        foreach ($headerRow as $index => $header) {
            // Excel puts some garbage at the beginning of the file that we need to filter out.
            $header = trim($header, "\xEF\xBB\xBF");
            $this->columns[] = $columnDescription = $this->getColumnDescription($header);

            if ($columnDescription == null) {
                if ($header == $idHeader) {
                    $this->indices['id'] = $index;
                } elseif ($header == $submissionIdHeader) {
                    $this->indices['submission_id'] = $index;
                } elseif (Str::startsWith($header, ['indigenous-', 'other-ethnicity-'])) {
                    $this->indices[$header] = $index;
                }
            }
        }

        $this->assert(array_key_exists('id', $this->indices), "No $idHeader column found");
        $this->assert(array_key_exists('submission_id', $this->indices), "No $submissionIdHeader column found");
    }

    /**
     * @throws AbortException
     */
    protected function getColumnDescription($header): ?array
    {
        if (! Str::startsWith($header, ['Paid_', 'Vol_'])) {
            return null;
        }

        /** @var string $columnTitlePrefix */
        $columnTitlePrefix = $this->collections->keys()->first(fn ($key) => Str::startsWith($header, $key));
        $collection = $this->collections[$columnTitlePrefix] ?? null;
        $this->assert(! empty($collection), 'Unknown collection: ' . $header);

        $demographicName = Str::substr($header, Str::length($columnTitlePrefix) + 1);
        $demographic = data_get(self::DEMOGRAPHICS, $demographicName);
        if (empty($demographic)) {
            if (Str::startsWith($demographicName, 'indigenous')) {
                $demographic = data_get(self::DEMOGRAPHICS, 'indigenous');
                $this->assert(! empty($this->indices[$demographicName]), 'Unknown demographic: ' . $header);
                $demographic['name'] = $this->indices[$demographicName];
            } elseif (Str::startsWith($demographicName, ['other-ethnicity', 'ethnicity-other'])) {
                $demographic = data_get(self::DEMOGRAPHICS, 'ethnicity-other');
                $this->assert(! empty($this->indices[$demographicName]), 'Unknown demographic: ' . $header);
                $demographic['name'] = $this->indices[$demographicName];
            } elseif (Str::startsWith($demographicName, ['ethnicity-unknown', 'ethnicity-decline'])) {
                $demographic = data_get(self::DEMOGRAPHICS, 'ethnicity-unknown');
            }

            $this->assert($demographic != null, 'Unknown demographic: ' . $header);
        }

        return ['collection' => $collection, 'demographic' => $demographic];
    }

    /**
     * @throws AbortException
     */
    protected function parseRow($csvRow, &$parseErrors): ?array
    {
        $row = [];
        foreach ($csvRow as $index => $cell) {
            $column = $this->columns[$index];
            if (empty($column)) {
                continue;
            }

            $collection = $column['collection'];
            $data = $this->getData($collection, $column['demographic'], $cell, $csvRow);
            if (! empty($data)) {
                $existingIndex = collect($row[$collection] ?? [])->search(
                    fn ($demographic) =>
                        $demographic['type'] === $data['type'] &&
                        $demographic['subtype'] === $data['subtype'] &&
                        $demographic['name'] === $data['name']
                );

                if ($existingIndex === false) {
                    $row[$collection][] = $data;
                } else {
                    data_set(
                        $row,
                        "$collection.$existingIndex.amount",
                        $data['amount'] + data_get($row, "$collection.$existingIndex.amount")
                    );
                }
            }
        }

        if (empty($row)) {
            return null;
        }

        $parentId = (int)$csvRow[$this->indices['id']];
        $submissionId = (int)$csvRow[$this->indices['submission_id']];

        $report = $this->modelConfig['model']::where(
            ['old_id' => $submissionId, 'old_model' => $this->modelConfig['old_model']]
        )->first();
        $this->assert(
            $report != null,
            "No report with submission id $submissionId found\n"
        );
        $this->assert(
            $report->{$this->modelConfig['parent']}?->ppc_external_id == $parentId,
            "Parent / Report ID mismatch: [Parent ID: $parentId, Submission ID: $submissionId]\n"
        );

        $row['report_uuid'] = $report->uuid;

        // Check that all the demographics are balanced
        $collections = array_merge(
            $this->modelConfig['model']::DEMOGRAPHIC_COLLECTIONS[Demographic::WORKDAY_TYPE]['paid'],
            $this->modelConfig['model']::DEMOGRAPHIC_COLLECTIONS[Demographic::WORKDAY_TYPE]['volunteer'],
        );
        foreach ($collections as $collection) {
            if (empty($row[$collection])) {
                continue;
            }

            $totals = ['gender' => 0, 'age' => 0, 'ethnicity' => 0];
            foreach ($row[$collection] as $demographic) {
                $totals[$demographic['type']] += $demographic['amount'];
            }

            if (collect($totals)->values()->unique()->count() > 1) {
                $message = "Demographics for collection are unbalanced\n";

                if ($totals['gender'] < $totals['age'] || $totals['gender'] < $totals['ethnicity']) {
                    $message .= "GENDER IS NOT THE LARGEST VALUE IN THIS COLLECTION\n";
                }

                $message .= json_encode([
                    'submission_id' => $submissionId,
                    'collection' => $collection,
                    'totals' => $totals,
                ], JSON_PRETTY_PRINT) . "\n";

                // We've decided go ahead and import unbalanced collections, but we do want to make sure we still
                // log the error in sequence with the rest.
                $parseErrors[] = new AbortException(ExceptionLevel::Warning, $message, 1);
            }
        }

        return $row;
    }

    /**
     * @throws AbortException
     */
    protected function getData($collection, $demographic, $cell, $row): array
    {
        if (empty($cell)) {
            return [];
        }

        if (! is_numeric($cell)) {
            $this->abort('Invalid value: ' .
                json_encode([
                    'collection' => $collection,
                    'demographic' => $demographic,
                    'cell' => $cell,
                ]) . "\n");
        }

        $demographic['amount'] = (int)round($cell);
        if (is_int($demographic['name'])) {
            // In this case, the "name" member is an index pointer to the column that holds the ethnicity
            // name.
            $name = $row[$demographic['name']];
            if (empty($name)) {
                $name = null;
            }
            $demographic['name'] = $name;
        }

        return $demographic;
    }

    protected function persistWorkdays($report, $data): void
    {
        $collections = array_merge(
            get_class($report)::DEMOGRAPHIC_COLLECTIONS[Demographic::WORKDAY_TYPE]['paid'],
            get_class($report)::DEMOGRAPHIC_COLLECTIONS[Demographic::WORKDAY_TYPE]['volunteer'],
        );

        $modelDescription = Str::replace('-', ' ', Str::title($report->shortName)) .
            ' (old_id=' . $report->old_id . ', uuid=' . $report->uuid . ')';
        $this->info("Persisting data for $modelDescription\n");
        foreach ($collections as $collection) {
            if (empty($data[$collection])) {
                continue;
            }

            if ($report->workdays()->collection($collection)->count() > 0) {
                $this->warn(
                    "WARNING!! Report already has demographics recorded for this collection, skipping!\n" .
                    "  collection: $collection\n" .
                    '  demographics: ' . json_encode($data[$collection], JSON_PRETTY_PRINT) . "\n"
                );

                continue;
            }

            $this->info("Populating collection $collection\n");
            $workday = Demographic::create([
                'demographical_type' => get_class($report),
                'demographical_id' => $report->id,
                'type' => Demographic::WORKDAY_TYPE,
                'collection' => $collection,
            ]);

            foreach ($data[$collection] as $demographicData) {
                $workday->entries()->create($demographicData);
            }
        }

        $this->info("Persistence complete for $modelDescription\n\n");
    }
}
