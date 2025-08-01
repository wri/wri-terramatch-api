<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use Illuminate\Console\Command;

class UpdatePlantingStatusFromCsvCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:update-planting-status-from-csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'One-off command: Update planting_status field for Projects, SitePolygons, and Sites from CSV files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting planting status updates from CSV files...');

        // Update Projects
        $this->updateProjectsPlantingStatus();

        // Update Sites
        $this->updateSitesPlantingStatus();

        // Update SitePolygons
        $this->updateSitePolygonsPlantingStatus();

        $this->info('All planting status updates completed successfully!');

        return 0;
    }

    /**
     * Update Projects planting_status from CSV
     */
    private function updateProjectsPlantingStatus()
    {
        $this->info('Processing Projects planting status...');

        $path = base_path('imports/tf_approved_project_planting_status.csv');

        if (! file_exists($path) || ! is_readable($path)) {
            $this->error("CSV file not found or not readable at: $path");

            return;
        }

        $rows = $this->readCsvFile($path);
        $updatedCount = 0;
        $errorCount = 0;

        foreach ($rows as $index => $row) {
            $this->info('Processing Project row #' . ($index + 1) . ': ' . json_encode($row));

            $project = Project::isUuid($row['uuid'])->first();

            if (! $project) {
                $this->error('Project not found for UUID: ' . $row['uuid']);
                $errorCount++;

                continue;
            }

            try {
                $project->planting_status = $row['planting_status'];
                $project->save();
                $updatedCount++;
                $this->info("Updated Project: {$project->name} (UUID: {$project->uuid})");
            } catch (\Exception $e) {
                $this->error('Error updating project planting status: ' . $e->getMessage());
                $errorCount++;

                continue;
            }
        }

        $this->info("Projects processing complete. Updated: $updatedCount, Errors: $errorCount");
    }

    /**
     * Update SitePolygons planting_status from CSV
     */
    private function updateSitePolygonsPlantingStatus()
    {
        $this->info('Processing SitePolygons planting status...');

        $path = base_path('imports/tf_approved_site_polygon_planting_status.csv');

        if (! file_exists($path) || ! is_readable($path)) {
            $this->error("CSV file not found or not readable at: $path");

            return;
        }

        $rows = $this->readCsvFile($path);
        $updatedCount = 0;
        $errorCount = 0;

        foreach ($rows as $index => $row) {
            $this->info('Processing SitePolygon row #' . ($index + 1) . ': ' . json_encode($row));

            $sitePolygon = SitePolygon::isUuid($row['uuid'])->first();

            if (! $sitePolygon) {
                $this->error('SitePolygon not found for UUID: ' . $row['uuid']);
                $errorCount++;

                continue;
            }

            try {
                $sitePolygon->planting_status = $row['planting_status'];
                $sitePolygon->save();
                $updatedCount++;
                $this->info("Updated SitePolygon: {$sitePolygon->poly_name} (UUID: {$sitePolygon->uuid})");
            } catch (\Exception $e) {
                $this->error('Error updating site polygon planting status: ' . $e->getMessage());
                $errorCount++;

                continue;
            }
        }

        $this->info("SitePolygons processing complete. Updated: $updatedCount, Errors: $errorCount");
    }

    /**
     * Update Sites planting_status from CSV
     */
    private function updateSitesPlantingStatus()
    {
        $this->info('Processing Sites planting status...');

        $path = base_path('imports/tf_approved_sites_planting_status.csv');

        if (! file_exists($path) || ! is_readable($path)) {
            $this->error("CSV file not found or not readable at: $path");

            return;
        }

        $rows = $this->readCsvFile($path);
        $updatedCount = 0;
        $errorCount = 0;

        foreach ($rows as $index => $row) {
            $this->info('Processing Site row #' . ($index + 1) . ': ' . json_encode($row));

            $site = Site::isUuid($row['uuid'])->first();

            if (! $site) {
                $this->error('Site not found for UUID: ' . $row['uuid']);
                $errorCount++;

                continue;
            }

            try {
                $site->planting_status = $row['planting_status'];
                $site->save();
                $updatedCount++;
                $this->info("Updated Site: {$site->name} (UUID: {$site->uuid})");
            } catch (\Exception $e) {
                $this->error('Error updating site planting status: ' . $e->getMessage());
                $errorCount++;

                continue;
            }
        }

        $this->info("Sites processing complete. Updated: $updatedCount, Errors: $errorCount");
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
