<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use Illuminate\Console\Command;

class UpdatePlantingStatusFromCsvV2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:update-planting-status-from-csv-v2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-off command: Update planting_status field for Project Reports,Sites from CSV files';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting planting status updates from CSV files...');

        // Update Project Reports
        $this->updateProjectReportsPlantingStatus();


        // Update Site Reports
        $this->updateSiteReportsPlantingStatus();

        $this->info('All planting status updates completed successfully!');

        return 0;
    }

    /**
     * Update Projects planting_status from CSV
     */
    private function updateProjectReportsPlantingStatus(): void
    {
        $this->info('Processing Projects Reports planting status...');

        $path = base_path('imports/project_report_planting_status_import.csv');

        if (! file_exists($path) || ! is_readable($path)) {
            $this->error("CSV file not found or not readable at: $path");

            return;
        }

        $rows = $this->readCsvFile($path);
        $updatedCount = 0;
        $errorCount = 0;

        foreach ($rows as $index => $row) {
            $this->info('Processing Project row #' . ($index + 1) . ': ' . json_encode($row));

            $projectReport = ProjectReport::isUuid($row['uuid'])->first();

            if (! $projectReport) {
                $this->error('Project not found for UUID: ' . $row['uuid']);
                $errorCount++;

                continue;
            }

            try {
                $projectReport->planting_status = $row['planting_status'];
                $projectReport->save();
                $updatedCount++;
                $this->info("Updated Project Report: {$projectReport->name} (UUID: {$projectReport->uuid})");
            } catch (\Exception $e) {
                $this->error('Error updating project planting status: ' . $e->getMessage());
                $errorCount++;

                continue;
            }
        }

        $this->info("Project Reports processing complete. Updated: $updatedCount, Errors: $errorCount");
    }

    /**
     * Update SiteReports planting_status from CSV
     */
    private function updateSiteReportsPlantingStatus(): void
    {
        $this->info('Processing SiteReport status...');

        $path = base_path('imports/site_report_planting_status_import.csv');

        if (! file_exists($path) || ! is_readable($path)) {
            $this->error("CSV file not found or not readable at: $path");

            return;
        }

        $rows = $this->readCsvFile($path);
        $updatedCount = 0;
        $errorCount = 0;

        foreach ($rows as $index => $row) {
            $this->info('Processing SiteReport row #' . ($index + 1) . ': ' . json_encode($row));

            $siteReport = SiteReport::isUuid($row['uuid'])->first();

            if (! $siteReport) {
                $this->error('SiteReport not found for UUID: ' . $row['uuid']);
                $errorCount++;

                continue;
            }

            try {
                $siteReport->planting_status = $row['planting_status'];
                $siteReport->save();
                $updatedCount++;
                $this->info("Updated SiteReport: {$siteReport->poly_name} (UUID: {$siteReport->uuid})");
            } catch (\Exception $e) {
                $this->error('Error updating site report planting status: ' . $e->getMessage());
                $errorCount++;

                continue;
            }
        }

        $this->info("SiteReports processing complete. Updated: $updatedCount, Errors: $errorCount");
    }

    /**
     * Read CSV file and return array of rows
     */
    private function readCsvFile(string $path): array
    {
        $header = null;
        $rows = [];

        if (($handle = fopen($path, 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (! $header) {
                    $header = $data; // First row is the header
                } else {
                    $rows[] = array_combine($header, $data);
                }
            }
            fclose($handle);
        }

        return $rows;
    }
}
