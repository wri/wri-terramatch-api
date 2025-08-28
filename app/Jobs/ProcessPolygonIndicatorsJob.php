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

    protected $targetSlugs;

    public function __construct(string $delayed_job_id, array $polygonUuids, array $options = [], array $targetSlugs = [])
    {
        $this->delayed_job_id = $delayed_job_id;
        $this->polygonUuids = $polygonUuids;
        $this->options = $options;
        $this->targetSlugs = $targetSlugs;
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

            foreach ($this->polygonUuids as $polygonUuid) {
                try {
                    $results = $indicatorService->updateIndicatorsForPolygon($polygonUuid, $this->targetSlugs);

                    foreach ($results as $slug => $result) {
                        if ($result['status'] === 'success') {
                            $successfulIndicators++;
                        } else {
                            $failedIndicators++;
                        }
                    }

                    Log::debug('Processed polygon indicators', [
                        'polygon_uuid' => $polygonUuid,
                        'results' => $results,
                    ]);

                } catch (Exception $e) {
                    $failedIndicators++;
                    Log::error('Failed to process indicators for polygon', [
                        'polygon_uuid' => $polygonUuid,
                        'error' => $e->getMessage(),
                    ]);
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

            Log::info('Polygon indicators processing completed', [
                'delayed_job_id' => $this->delayed_job_id,
                'total_polygons' => $totalPolygons,
                'successful_indicators' => $successfulIndicators,
                'failed_indicators' => $failedIndicators,
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
