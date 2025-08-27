<?php

namespace App\Jobs;

use App\Models\DelayedJob;
use App\Services\IndicatorUpdateService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPolygonIndicatorsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    protected $delayed_job_id;

    protected $polygonUuids;

    protected $options;

    public function __construct(string $delayed_job_id, array $polygonUuids, array $options = [])
    {
        $this->delayed_job_id = $delayed_job_id;
        $this->polygonUuids = $polygonUuids;
        $this->options = $options;
    }

    public function handle(IndicatorUpdateService $indicatorService)
    {
        try {
            $delayedJob = DelayedJob::findOrFail($this->delayed_job_id);

            $delayedJob->update([
                'status' => 'processing',
                'payload' => ['message' => 'Processing polygon indicators'],
            ]);

            $totalPolygons = count($this->polygonUuids);
            $successfulIndicators = 0;
            $failedIndicators = 0;

            Log::info('Starting polygon indicators processing', [
                'delayed_job_id' => $this->delayed_job_id,
                'total_polygons' => $totalPolygons,
            ]);

            $batchSize = 50;
            $polygonBatches = array_chunk($this->polygonUuids, $batchSize);

            foreach ($polygonBatches as $batchIndex => $polygonBatch) {
                $batchResults = $indicatorService->updateIndicatorsForPolygonBatch($polygonBatch);
                
                foreach ($batchResults as $polygonUuid => $results) {
                    foreach ($results as $slug => $result) {
                        if ($result['status'] === 'success') {
                            $successfulIndicators++;
                        } else {
                            $failedIndicators++;
                        }
                    }
                }

                $processedSoFar = ($batchIndex + 1) * $batchSize;
                $processedSoFar = min($processedSoFar, $totalPolygons);
                
                $delayedJob->update([
                    'payload' => [
                        'message' => "Processing polygon indicators: {$processedSoFar}/{$totalPolygons}",
                        'progress' => round(($processedSoFar / $totalPolygons) * 100, 2),
                    ],
                ]);

                if ($batchIndex < count($polygonBatches) - 1) {
                    sleep(1);
                }
            }

            $delayedJob->update([
                'status' => 'succeeded',
                'payload' => [
                    'message' => 'Polygon indicators processing completed',
                    'total_polygons' => $totalPolygons,
                    'successful_indicators' => $successfulIndicators,
                    'failed_indicators' => $failedIndicators,
                ],
                'status_code' => Response::HTTP_OK,
            ]);

        } catch (Exception $e) {
            Log::error('Failed to process polygon indicators job', [
                'delayed_job_id' => $this->delayed_job_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($delayedJob)) {
                $delayedJob->update([
                    'status' => 'failed',
                    'payload' => [
                        'error' => $e->getMessage(),
                        'total_polygons' => count($this->polygonUuids),
                    ],
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                ]);
            }
        }
    }
}
