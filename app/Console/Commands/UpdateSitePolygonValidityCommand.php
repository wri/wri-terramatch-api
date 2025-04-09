<?php

namespace App\Console\Commands;

use App\Models\V2\Sites\SitePolygon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateSitePolygonValidityCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'site-polygon:update-validity {--only-null : Update only records with null is_valid} {--batch-size=100 : Number of records to process in each batch}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update is_valid column in site_polygon table based on criteria_site data';

    /**
     * Criteria IDs that are excluded from validation failure
     *
     * @var array
     */
    protected const EXCLUDED_VALIDATION_CRITERIA = [7, 12, 14];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $onlyNull = $this->option('only-null');
        $batchSize = (int) $this->option('batch-size');

        $this->info('Starting site_polygon validity update process');
        Log::info('Starting site_polygon validity update process');

        // Query builder for site_polygon
        $query = SitePolygon::query();

        // Apply null filter if requested
        if ($onlyNull) {
            $query->whereNull('is_valid');
            $this->info('Processing only records with NULL is_valid value');
            Log::info('Processing only records with NULL is_valid value');
        }

        // Get total count for progress bar
        $totalPolygons = $query->count();
        $this->info("Found {$totalPolygons} records to process");
        Log::info("Found {$totalPolygons} records to process");

        if ($totalPolygons === 0) {
            $this->info('No records to process.');

            return 0;
        }

        // Create progress bar
        $progressBar = $this->output->createProgressBar($totalPolygons);
        $progressBar->start();

        // Process in batches
        $processed = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $query->chunkById($batchSize, function ($polygons) use (&$processed, &$updated, &$skipped, &$errors, $progressBar) {
            foreach ($polygons as $polygon) {
                try {
                    $result = $this->updateSitePolygonValidity($polygon->poly_id);

                    if ($result === 'updated') {
                        $updated++;
                    } elseif ($result === 'skipped') {
                        $skipped++;
                    }

                    $processed++;
                    $progressBar->advance();
                } catch (\Exception $e) {
                    $errors++;
                    Log::error("Error processing polygon ID: {$polygon->poly_id} - " . $e->getMessage());
                    $this->error("Error processing polygon ID: {$polygon->poly_id} - " . $e->getMessage());
                }
            }

            // Log progress after each batch
            Log::info("Progress: Processed {$processed} records, Updated: {$updated}, Skipped: {$skipped}, Errors: {$errors}");
        });

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Process completed. Total processed: {$processed}, Updated: {$updated}, Skipped: {$skipped}, Errors: {$errors}");
        Log::info("Process completed. Total processed: {$processed}, Updated: {$updated}, Skipped: {$skipped}, Errors: {$errors}");

        return 0;
    }

    /**
     * Update the validity status of a single site polygon
     *
     * @param string $polygonId
     * @return string Status: 'updated' or 'skipped'
     */
    protected function updateSitePolygonValidity(string $polygonId): string
    {
        $sitePolygon = SitePolygon::forPolygonGeometry($polygonId)->first();
        if (! $sitePolygon) {
            Log::warning("SitePolygon not found for polygon ID: {$polygonId}");

            return 'skipped';
        }

        $allCriteria = DB::table('criteria_site')->where('polygon_id', $polygonId)->get();
        $originalIsValid = $sitePolygon->is_valid;

        if ($allCriteria->isEmpty()) {
            $sitePolygon->is_valid = null; // not checked
            $sitePolygon->save();

            if ($originalIsValid !== null) {
                Log::info("Updated polygon {$polygonId}: from '{$originalIsValid}' to NULL (no criteria)");
            }

            return 'updated';
        }

        $hasAnyFailing = $allCriteria->contains(function ($c) {
            return $c->valid === 0 || $c->valid === false;
        });

        if (! $hasAnyFailing) {
            $newIsValid = 'passed';
        } else {
            $excludedCriteria = $allCriteria->filter(function ($c) {
                return in_array($c->criteria_id, self::EXCLUDED_VALIDATION_CRITERIA);
            });

            $nonExcludedCriteria = $allCriteria->filter(function ($c) {
                return ! in_array($c->criteria_id, self::EXCLUDED_VALIDATION_CRITERIA);
            });

            $hasFailingNonExcluded = $nonExcludedCriteria->contains(function ($c) {
                return $c->valid === 0 || $c->valid === false;
            });

            if ($hasFailingNonExcluded) {
                $newIsValid = 'failed';
            } else {
                $newIsValid = 'partial';
            }
        }

        // Check if value would change before saving
        if ($sitePolygon->is_valid !== $newIsValid) {
            $sitePolygon->is_valid = $newIsValid;
            $sitePolygon->save();

            Log::info("Updated polygon {$polygonId}: from '" .
                ($originalIsValid ?? 'NULL') . "' to '{$newIsValid}'");

            return 'updated';
        }

        return 'skipped';
    }
}
