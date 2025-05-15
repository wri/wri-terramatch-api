<?php

namespace App\Console\Commands;

use App\Models\V2\Sites\SitePolygon;
use App\Services\RunIndicatorAnalysisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RunIndicatorAnalysisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run-indicator-analysis {--slugs=*} {--force} {--batch-size=100} {--skip=0} {--update-existing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run indicator analysis for some slugs example: php artisan run-indicator-analysis --slugs=restorationByLandUse --slugs=restorationByStrategy, etc. Use --update-existing to update records that already have values.';

    protected RunIndicatorAnalysisService $service;

    public function __construct(RunIndicatorAnalysisService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle(): int
    {
        $this->info('Running indicator analysis');

        // The slug options to use are as follows:
        // --slugs=treeCoverLoss
        // --slugs=treeCoverLossFires
        // --slugs=restorationByEcoRegion
        // --slugs=restorationByStrategy
        // --slugs=restorationByLandUse

        $slugs = $this->option('slugs');
        $force = $this->option('force');
        $batchSize = (int) $this->option('batch-size');
        $skip = (int) $this->option('skip');
        $updateExisting = $this->option('update-existing');

        if (empty($slugs)) {
            $this->error('No slugs provided. Please use --slugs=slug1 --slugs=slug2 ...');

            return 1;
        }

        // Get total count for progress tracking
        $totalPolygons = SitePolygon::where('is_active', true)
            ->where('status', 'approved')
            ->count();

        $this->info("Total eligible polygons: $totalPolygons");

        // Log startup info with options
        Log::info('Starting indicator analysis', [
            'slugs' => $slugs,
            'force' => $force,
            'batch_size' => $batchSize,
            'skip' => $skip,
            'update_existing' => $updateExisting,
            'total_polygons' => $totalPolygons,
        ]);

        if ($updateExisting) {
            $this->info("Update-existing mode: Will only update records that already exist in the database");
            
            // Store UUIDs of polygons that need updating for each slug
            $polygonUuidsToUpdate = [];
            
            foreach ($slugs as $slug) {
                // Get the table name based on the slug
                $tableName = '';
                if (str_contains($slug, 'treeCoverLoss')) {
                    $tableName = 'indicator_output_tree_cover_loss';
                } elseif (str_contains($slug, 'restorationBy')) {
                    $tableName = 'indicator_output_hectares';
                }
                
                if (empty($tableName)) {
                    continue;
                }
                
                // Get existing records and join with site_polygon to get the poly_ids
                $records = DB::table($tableName . ' as i')
                    ->join('site_polygon as sp', 'i.site_polygon_id', '=', 'sp.id')
                    ->where('i.indicator_slug', $slug)
                    ->select('i.id', 'i.site_polygon_id', 'sp.poly_id')
                    ->get();
                
                $this->info("Found " . count($records) . " existing records for slug: " . $slug);
                
                // Extract the poly_ids (UUIDs) for processing
                $polyIds = $records->pluck('poly_id')->toArray();
                
                if (!empty($polyIds)) {
                    $polygonUuidsToUpdate = array_merge($polygonUuidsToUpdate, $polyIds);
                    $this->info("Added " . count($polyIds) . " polygon UUIDs for processing");
                } else {
                    $this->warn("No existing records found for slug: " . $slug . ". No updates will be performed for this slug.");
                }
            }
            
            // Remove duplicates
            $polygonUuidsToUpdate = array_unique($polygonUuidsToUpdate);
            $finalCount = count($polygonUuidsToUpdate);
            
            $this->info("Will process a total of $finalCount polygons for updating");
            
            if ($finalCount == 0) {
                $this->warn("No existing records found to update. Command will exit.");
                return 0;
            }
            
            // Process directly the batches of polygons with UUIDs we found
            $batchStartTime = microtime(true);
            $overallStartTime = $batchStartTime;
            $totalProcessed = 0;
            
            // Process in batches
            foreach (array_chunk($polygonUuidsToUpdate, $batchSize) as $batchUuids) {
                $batchCount = count($batchUuids);
                $this->info("Processing batch of $batchCount polygons (total processed: $totalProcessed)");
                
                $request = [
                    'uuids' => $batchUuids,
                    'force' => true, // Force update
                    'update_existing' => true,
                ];
                
                foreach ($slugs as $slug) {
                    $this->info('Analysis started for slug: ' . $slug);
                    
                    try {
                        $response = $this->service->run($request, $slug);
                        $responseData = json_decode($response->getContent(), true);
                        
                        if (isset($responseData['stats'])) {
                            $this->table(
                                ['Total', 'Processed', 'Skipped', 'Errors'],
                                [[$responseData['stats']['total_polygons'],
                                  $responseData['stats']['processed'],
                                  $responseData['stats']['skipped'],
                                  $responseData['stats']['errors']]]
                            );
                            
                            $this->info("Successfully updated {$responseData['stats']['processed']} existing records for slug: " . $slug);
                        }
                    } catch (\Exception $e) {
                        $this->error('Error during analysis: ' . $e->getMessage());
                        Log::error('Error during analysis for slug: ' . $slug, [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                    
                    $this->info('Analysis finished for slug: ' . $slug);
                }
                
                $totalProcessed += $batchCount;
                
                // Calculate and display performance metrics
                $batchEndTime = microtime(true);
                $batchDuration = $batchEndTime - $batchStartTime;
                $recordsPerSecond = $batchCount / $batchDuration;
                
                $this->info(sprintf(
                    'Batch completed in %.2f seconds (%.2f records/sec)',
                    $batchDuration,
                    $recordsPerSecond
                ));
                
                $batchStartTime = microtime(true);
            }
            
            $overallDuration = microtime(true) - $overallStartTime;
            $this->info(sprintf(
                'Complete! Processed %d polygons in %.2f seconds',
                $totalProcessed,
                $overallDuration
            ));
            
            Log::info('Indicator analysis completed', [
                'total_processed' => $totalProcessed,
                'duration_seconds' => $overallDuration,
            ]);
            
            return 0;
        }
        
        // Continue with normal processing for non-update_existing mode
        // Process in batches to avoid memory issues
        $batchStartTime = microtime(true);
        $overallStartTime = $batchStartTime;
        $totalProcessed = 0;

        // Define query for normal processing mode
        $polygonsToProcess = SitePolygon::where('is_active', true)
            ->where('status', 'approved');

        $polygonsToProcess->orderBy('id')
            ->when($skip > 0, function ($query) use ($skip) {
                return $query->skip($skip);
            })
            ->chunk($batchSize, function ($polygons) use ($slugs, $force, &$totalProcessed, &$batchStartTime, $batchSize, $updateExisting) {
                $polygonsUuids = $polygons->pluck('poly_id')->toArray();
                $batchCount = count($polygonsUuids);

                $this->info("Processing batch of $batchCount polygons (total processed: $totalProcessed)");

                $request = [
                    'uuids' => $polygonsUuids,
                    'force' => $force,
                    'update_existing' => $updateExisting,
                ];

                foreach ($slugs as $slug) {
                    $this->info('Analysis started for slug: ' . $slug);

                    try {
                        $response = $this->service->run($request, $slug);
                        $responseData = json_decode($response->getContent(), true);

                        if (isset($responseData['stats'])) {
                            $this->table(
                                ['Total', 'Processed', 'Skipped', 'Errors'],
                                [[$responseData['stats']['total_polygons'],
                                  $responseData['stats']['processed'],
                                  $responseData['stats']['skipped'],
                                  $responseData['stats']['errors']]]
                            );
                            
                            if ($updateExisting) {
                                $this->info("Successfully updated {$responseData['stats']['processed']} existing records for slug: " . $slug);
                            }
                        }
                    } catch (\Exception $e) {
                        $this->error('Error during analysis: ' . $e->getMessage());
                        Log::error('Error during analysis for slug: ' . $slug, [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                    $this->info('Analysis finished for slug: ' . $slug);
                }

                $totalProcessed += $batchCount;

                // Calculate and display performance metrics
                $batchEndTime = microtime(true);
                $batchDuration = $batchEndTime - $batchStartTime;
                $recordsPerSecond = $batchCount / $batchDuration;

                $this->info(sprintf(
                    'Batch completed in %.2f seconds (%.2f records/sec)',
                    $batchDuration,
                    $recordsPerSecond
                ));

                $batchStartTime = microtime(true);
            });

        $overallDuration = microtime(true) - $overallStartTime;
        $this->info(sprintf(
            'Complete! Processed %d polygons in %.2f seconds',
            $totalProcessed,
            $overallDuration
        ));

        Log::info('Indicator analysis completed', [
            'total_processed' => $totalProcessed,
            'duration_seconds' => $overallDuration,
        ]);

        return 0;
    }
}
