<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\Abortable;
use App\Models\SiteSubmission;
use App\Models\Submission;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Workdays\Workday;
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
            'Paid_site_establishment' => Workday::COLLECTION_SITE_PAID_SITE_ESTABLISHMENT,
            'Vol_site_establishment' => Workday::COLLECTION_SITE_VOLUNTEER_SITE_ESTABLISHMENT,
            'Paid_planting' => Workday::COLLECTION_SITE_PAID_PLANTING,
            'Vol_planting' => Workday::COLLECTION_SITE_VOLUNTEER_PLANTING,
            'Paid_monitoring' => Workday::COLLECTION_SITE_PAID_SITE_MONITORING,
            'Vol_monitoring' => Workday::COLLECTION_SITE_VOLUNTEER_SITE_MONITORING,
            'Paid_maintenance' => Workday::COLLECTION_SITE_PAID_SITE_MAINTENANCE,
            'Vol_maintenance' => Workday::COLLECTION_SITE_VOLUNTEER_SITE_MAINTENANCE,
            'Paid_other' => Workday::COLLECTION_SITE_PAID_OTHER,
            'Vol_other' => Workday::COLLECTION_SITE_VOLUNTEER_OTHER,
        ],

        'projects' => [
            'Paid_project_management' => Workday::COLLECTION_PROJECT_PAID_PROJECT_MANAGEMENT,
            'Vol_project_management' => Workday::COLLECTION_PROJECT_VOLUNTEER_PROJECT_MANAGEMENT,
            'Paid_nursery_ops' => Workday::COLLECTION_PROJECT_PAID_NURSERY_OPERATIONS,
            'Vol_nursery_ops' => Workday::COLLECTION_PROJECT_VOLUNTEER_NURSERY_OPERATIONS,
            'Paid_seed_collection' => Workday::COLLECTION_PROJECT_PAID_NURSERY_OPERATIONS,
            'Vol_seed_collection' => Workday::COLLECTION_PROJECT_VOLUNTEER_NURSERY_OPERATIONS,
            'Paid_other' => Workday::COLLECTION_PROJECT_PAID_OTHER,
            'Vol_other' => Workday::COLLECTION_PROJECT_VOLUNTEER_OTHER,
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
        'women' => ['type' => 'gender', 'subtype' => null, 'name' => 'female'],
        'men' => ['type' => 'gender', 'subtype' => null, 'name' => 'male'],
        'non-binary' => ['type' => 'gender', 'subtype' => null, 'name' => 'non-binary'],
        'nonbinary' => ['type' => 'gender', 'subtype' => null, 'name' => 'non-binary'],
        'gender-unknown' => ['type' => 'gender', 'subtype' => null, 'name' => 'unknown'],
        'no-gender' => ['type' => 'gender', 'subtype' => null, 'name' => 'unknown'],

        'youth_15-24' => ['type' => 'age', 'subtype' => null, 'name' => 'youth'],
        'adult_24-64' => ['type' => 'age', 'subtype' => null, 'name' => 'adult'],
        'elder_65+' => ['type' => 'age', 'subtype' => null, 'name' => 'elder'],
        'age-unknown' => ['type' => 'age', 'subtype' => null, 'name' => 'unknown'],
        'age_unknown' => ['type' => 'age', 'subtype' => null, 'name' => 'unknown'],

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
    public function handle()
    {
        $type = $this->argument('type');

        $this->assert(! empty(self::COLLECTIONS[$type]), "Unknown type: $type");
        $this->modelConfig = self::MODEL_CONFIGS[$type];
        $this->collections = collect(self::COLLECTIONS[$type]);

        $fileHandle = fopen($this->argument('file'), 'r');
        $this->parseHeaders(fgetcsv($fileHandle));

        $rows = collect();
        while ($csvRow = fgetcsv($fileHandle)) {
            $rows->push($this->parseRow($csvRow));
        }
        $rows = $rows->filter();
        fclose($fileHandle);

        if ($this->option('dry-run')) {
            echo json_encode($rows, JSON_PRETTY_PRINT) . "\n\n";
        } else {
            // A separate loop so we can validate as much input as possible before we start persisting any records
            foreach ($rows as $reportData) {
                $report = $this->modelConfig['model']::isUuid($reportData['report_uuid'])->first();
                $this->persistWorkdays($report, $reportData);
            }

            echo "Workday import complete!\n\n";
        }
    }

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

    protected function parseRow($csvRow): ?array
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
                $combinedRecords = false;
                foreach ($row[$collection] ?? [] as &$existingData) {
                    if ($existingData['type'] == $data['type'] &&
                        $existingData['subtype'] == $data['subtype'] &&
                        $existingData['name'] == $data['name']) {
                        $combinedRecords = true;
                        $existingData['amount'] += $data['amount'];
                        break;
                    }
                }
                if (!$combinedRecords) {
                    $row[$collection][] = $data;
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
            $report != null && $report->{$this->modelConfig['parent']}?->ppc_external_id == $parentId,
            "Parent / Report ID mismatch: [Parent ID: $parentId, Submission ID: $submissionId]"
        );

        $row['report_uuid'] = $report->uuid;

        return $row;
    }

    protected function getData($collection, $demographic, $cell, $row): array
    {
        if (empty($cell)) {
            return [];
        }

        if (! is_numeric($cell) || ('' . (int)$cell) != trim($cell)) {
            $this->abort('Invalid value: ' .
                json_encode([
                    'collection' => $collection,
                    'demographic' => $demographic,
                    'cell' => $cell,
                ]));
        }

        $demographic['amount'] = (int)$cell;
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
            get_class($report)::WORKDAY_COLLECTIONS['paid'],
            get_class($report)::WORKDAY_COLLECTIONS['volunteer'],
        );

        $modelDescription = Str::replace('-', ' ', Str::title($report->shortName)) .
            ' (old_id=' . $report->old_id . ', uuid=' . $report->uuid . ')';
        echo "Persisting data for $modelDescription\n";
        foreach ($collections as $collection) {
            if (empty($data[$collection])) {
                continue;
            }

            if ($report->workdays()->collection($collection)->count() > 0) {
                echo "WARNING!! Report already has demographics recorded for this collection, skipping!\n";
                echo "  collection: $collection\n";
                echo '  demographics: ' . json_encode($data[$collection], JSON_PRETTY_PRINT) . "\n";

                continue;
            }

            echo "Populating collection $collection\n";
            $workday = Workday::create([
                'workdayable_type' => get_class($report),
                'workdayable_id' => $report->id,
                'collection' => $collection,
            ]);

            foreach ($data[$collection] as $demographicData) {
                $workday->demographics()->create($demographicData);
            }
        }

        echo "Persistence complete for $modelDescription\n\n";
    }
}
