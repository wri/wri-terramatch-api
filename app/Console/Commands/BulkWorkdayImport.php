<?php

namespace App\Console\Commands;

use App\Models\SiteSubmission;
use App\Models\Submission;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Workdays\Workday;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\NoReturn;

class BulkWorkdayImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bulk-workday-import {type} {file}';

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
            'Paid_nursery_operations' => Workday::COLLECTION_PROJECT_PAID_NURSERY_OPERATIONS,
            'Vol_nursery_operations' => Workday::COLLECTION_PROJECT_VOLUNTEER_NURSERY_OPERATIONS,
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
        'gender-unknown' => ['type' => 'gender', 'subtype' => null, 'name' => 'unknown'],

        'youth_15-24' => ['type' => 'age', 'subtype' => null, 'name' => 'youth'],
        'adult_24-64' => ['type' => 'age', 'subtype' => null, 'name' => 'adult'],
        'elder_65+' => ['type' => 'age', 'subtype' => null, 'name' => 'elder'],
        'age-unknown' => ['type' => 'age', 'subtype' => null, 'name' => 'unknown'],

        'indigenous' => ['type' => 'ethnicity', 'subtype' => 'indigenous', 'name' => null],
        'ethnicity-other' => ['type' => 'ethnicity', 'subtype' => 'other', 'name' => null],
        'ethnicity-unknown' => ['type' => 'ethnicity', 'subtype' => 'unknown', 'name' => null],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        if (empty(self::COLLECTIONS[$type])) {
            $this->abort("Unknown type: $type");
        }

        $file_handle = fopen($this->argument('file'), 'r');

        $columns = [];
        $csvRow = fgetcsv($file_handle);
        $idIndex = -1;
        $submissionIdIndex = -1;
        $modelConfig = self::MODEL_CONFIGS[$type];
        foreach ($csvRow as $index => $header) {
            if ($header == $modelConfig['id']) {
                $idIndex = $index;
                $columns[] = null;
            } elseif ($header == $modelConfig['submission_id']) {
                $submissionIdIndex = $index;
                $columns[] = null;
            } else {
                $columns[] = $this->getColumnDescription($type, $header);
            }
        }

        if ($idIndex < 0) {
            $this->abort('No ' . $modelConfig['id'] . ' column found');
        }
        if ($submissionIdIndex < 0) {
            $this->abort('No '. $modelConfig['submission_id'] . ' column found');
        }

        $rows = [];
        while ($csvRow = fgetcsv($file_handle)) {
            $row = [];
            foreach ($csvRow as $index => $cell) {
                $column = $columns[$index];
                if (empty($column)) {
                    continue;
                }

                $data = $this->getData($column['collection'], $column['demographic'], $cell);
                if (! empty($data)) {
                    $row[$column['collection']] = array_merge($row[$column['collection']] ?? [], $data);
                }
            }

            if (empty($row)) {
                continue;
            }

            $parentId = (int)$csvRow[$idIndex];
            $submissionId = (int)$csvRow[$submissionIdIndex];

            $report = $modelConfig['model']::where(
                ['old_id' => $submissionId, 'old_model' => $modelConfig['old_model']]
            )->first();
            if ($report == null || $report->{$modelConfig['parent']}?->ppc_external_id != $parentId) {
                $this->abort("Parent / Report ID mismatch: [Parent ID: $parentId, Submission ID: $submissionId]");
            }

            $row['report_uuid'] = $report->uuid;
            $rows[] = $row;
        }
        fclose($file_handle);

        // A separate loop so we can validate as much input as possible before we start persisting any records
        foreach ($rows as $reportData) {
            $report = $modelConfig['model']::isUuid($reportData['report_uuid'])->first();
            $this->persistWorkdays($report, $reportData);
        }
    }

    #[NoReturn]
    protected function abort(string $message, int $exitCode = 1): void
    {
        echo $message;
        exit($exitCode);
    }

    protected function getColumnDescription($type, $header): ?array
    {
        if (! Str::startsWith($header, ['Paid_', 'Vol_'])) {
            return null;
        }

        /** @var string $columnTitlePrefix */
        $columnTitlePrefix = collect(self::COLLECTIONS[$type])
            ->keys()
            ->first(fn ($key) => Str::startsWith($header, $key));
        $collection = data_get(self::COLLECTIONS, "$type.$columnTitlePrefix");
        if (empty($collection)) {
            $this->abort('Unknown collection: ' . $header);
        }

        $demographic = data_get(self::DEMOGRAPHICS, Str::substr($header, Str::length($columnTitlePrefix) + 1));
        if (empty($demographic)) {
            $this->abort('Unknown demographic: ' . $header);
        }

        return ['collection' => $collection, 'demographic' => $demographic];
    }

    protected function getData($collection, $demographic, $cell): array
    {
        if (empty($cell)) {
            return [];
        }

        if (empty($demographic['subtype'])) {
            if (! is_numeric($cell) || ('' . (int)$cell) != trim($cell)) {
                $this->abort('Invalid value: ' .
                    json_encode([
                        'collection' => $collection,
                        'demographic' => $demographic,
                        'cell' => $cell,
                    ]));
            }

            $demographic['amount'] = (int)$cell;

            return [$demographic];
        }

        return collect(explode('|', $cell))->map(function ($subCell) use ($demographic, $collection, $cell) {
            $parts = explode(':', $subCell);
            if (count($parts) != 2 || ! is_numeric($parts[1]) || ('' . (int)$parts[1]) != trim($parts[1])) {
                $this->abort('Invalid value: ' .
                    json_encode([
                        'collection' => $collection,
                        'demographic' => $demographic,
                        'cell' => $cell,
                        'parts' => $parts,
                    ]));
            }

            $demographic['name'] = trim($parts[0]);
            $demographic['amount'] = (int)$parts[1];

            return $demographic;
        })->toArray();
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
                echo '  demographics: ' . json_encode($data[$collection]) . "\n";

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
