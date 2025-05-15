<?php

namespace App\Console\Commands;

use App\Models\V2\Sites\SitePolygon;
use App\Services\RunIndicatorAnalysisService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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

        // Process in batches to avoid memory issues
        $batchStartTime = microtime(true);
        $overallStartTime = $batchStartTime;
        $totalProcessed = 0;

        SitePolygon::where('is_active', true)
            ->where('status', 'approved')
            ->orderBy('id')
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
