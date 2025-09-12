<?php

namespace App\Jobs;

use App\Mail\PolygonOperationsComplete;
use App\Models\DelayedJob;
use App\Models\DelayedJobProgress;
use App\Models\V2\Sites\Site;
use App\Services\PolygonService;
use App\Services\PolygonValidationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RunSitePolygonsValidationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 50;

    public $tries = 1;

    protected $uuid;

    protected $delayed_job_id;

    protected $sitePolygonsUuids;

    protected $chunkSize = 10;

    protected $memoryClearFrequency = 100;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $delayed_job_id, array $sitePolygonsUuids, int $chunkSize = 100)
    {
        $this->sitePolygonsUuids = $sitePolygonsUuids;
        $this->delayed_job_id = $delayed_job_id;
        $this->chunkSize = $chunkSize;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PolygonValidationService $validationService, PolygonService $polygonService)
    {
        try {
            $delayedJob = DelayedJobProgress::findOrFail($this->delayed_job_id);
            $user = $delayedJob->creator;
            $metadata = $delayedJob->metadata;

            if (! $user) {
                Log::warning('No creator found for delayed job: ' . $this->delayed_job_id);
            }

            $entityId = $metadata['entity_id'] ?? null;

            if (! $entityId) {
                Log::error('entityId is null, unable to find site');

                throw new Exception('Entity ID is null in delayed job metadata.');
            }

            $site = Site::findOrFail($entityId);

            $totalPolygons = count($this->sitePolygonsUuids);
            $processedCount = 0;
            $jobStartTime = microtime(true);
            $polygonChunks = array_chunk($this->sitePolygonsUuids, $this->chunkSize);

            foreach ($polygonChunks as $chunkIndex => $polygonChunk) {
                try {
                    foreach ($polygonChunk as $polygonIndex => $polygonUuid) {
                        $elapsedTime = microtime(true) - $jobStartTime;
                        if ($elapsedTime > 40) {
                            Log::warning("Job approaching timeout after {$elapsedTime}s, stopping at polygon {$processedCount}");

                            break 2;
                        }

                        $this->validateSinglePolygon($polygonUuid, $validationService, $polygonService);
                        $processedCount++;

                        $delayedJob->increment('processed_content');
                        $delayedJob->processMessage();

                        if (($polygonIndex + 1) % 2 === 0) {
                            $this->clearMemoryAndConnections();
                        }
                    }

                    $progress = min(100, round(($processedCount / $totalPolygons) * 100, 2));
                    $delayedJob->update(['progress' => $progress]);
                    $delayedJob->save();

                    $this->clearMemoryAndConnections();
                } catch (Exception $chunkException) {
                    Log::error('Error processing chunk ' . ($chunkIndex + 1) . ': ' . $chunkException->getMessage());
                    Log::error('Chunk exception trace: ' . $chunkException->getTraceAsString());

                    throw $chunkException;
                }
            }

            if ($processedCount < $totalPolygons) {
                $remainingPolygons = array_slice($this->sitePolygonsUuids, $processedCount);
                $continuationJob = new RunSitePolygonsValidationJob($this->delayed_job_id, $remainingPolygons);
                dispatch($continuationJob)->delay(now()->addSeconds(5));

                Log::info("Partial completion: processed {$processedCount}/{$totalPolygons} polygons. Scheduled continuation job.");

                $delayedJob->update([
                    'status' => DelayedJobProgress::STATUS_PENDING,
                    'payload' => ['message' => "Processed {$processedCount} of {$totalPolygons} polygons. Continuing..."],
                    'status_code' => Response::HTTP_OK,
                    'progress' => round(($processedCount / $totalPolygons) * 100, 2),
                ]);
            } else {
                $delayedJob->update([
                    'status' => DelayedJobProgress::STATUS_SUCCEEDED,
                    'payload' => ['message' => 'Validation completed for all site polygons'],
                    'status_code' => Response::HTTP_OK,
                    'progress' => 100,
                ]);
            }

            if ($user && $user->email_address) {
                Mail::to($user->email_address)
                    ->send(new PolygonOperationsComplete(
                        $site,
                        'Check',
                        $user,
                        now()
                    ));
            } else {
                Log::warning('User or email address not found, skipping email notification');
            }

        } catch (Exception $e) {
            Log::error('Error in RunSitePolygonsValidationJob: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            Log::error('Job data: delayed_job_id=' . $this->delayed_job_id . ', polygon_count=' . count($this->sitePolygonsUuids));

            try {
                DelayedJob::where('id', $this->delayed_job_id)->update([
                    'status' => DelayedJob::STATUS_FAILED,
                    'payload' => json_encode(['error' => $e->getMessage()]),
                    'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
                ]);
            } catch (Exception $updateException) {
                Log::error('Failed to update delayed job status: ' . $updateException->getMessage());
            }
        }
    }

    /**
     * Validate a single polygon with all validation rules
     *
     * @param string $polygonUuid
     * @param PolygonValidationService $validationService
     * @param PolygonService $polygonService
     * @return void
     */
    protected function validateSinglePolygon(string $polygonUuid, PolygonValidationService $validationService, PolygonService $polygonService): void
    {
        try {
            $request = new Request(['uuid' => $polygonUuid]);

            $validationService->validateOverlapping($request);
            $validationService->checkSelfIntersection($request);
            $validationService->validateCoordinateSystem($request);
            $validationService->validatePolygonSize($request);
            $validationService->checkWithinCountry($request);
            $validationService->checkBoundarySegments($request);
            $validationService->getGeometryType($request);
            $validationService->validateEstimatedArea($request);
            $validationService->validateDataInDB($request);
            $validationService->validatePlantStartDate($request);
            $polygonService->updateSitePolygonValidity($polygonUuid);
        } catch (Exception $e) {
            Log::error("Error validating polygon {$polygonUuid}: " . $e->getMessage());

            throw $e;
        }
    }

    /**
     * Clear memory and database connections to prevent memory leaks
     *
     * @return void
     */
    protected function clearMemoryAndConnections(): void
    {
        try {
            DB::disconnect();
            gc_collect_cycles();
            if (function_exists('gc_mem_caches')) {
                gc_mem_caches();
            }
        } catch (Exception $e) {
            Log::warning('Error during memory cleanup: ' . $e->getMessage());
        }
    }
}
