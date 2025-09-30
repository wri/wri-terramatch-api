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

    public $timeout = 55;

    public $tries = 1;

    protected $uuid;

    protected $delayed_job_id;

    protected $sitePolygonsUuids;

    protected $chunkSize = 10;

    protected $memoryClearFrequency = 5;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $delayed_job_id, array $sitePolygonsUuids, int $chunkSize = 10)
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
                        if ($elapsedTime > 50) {
                            Log::warning("Job approaching timeout after {$elapsedTime}s, stopping at polygon {$processedCount}");

                            break 2;
                        }

                        try {
                            $this->validateSinglePolygon($polygonUuid, $validationService, $polygonService);
                            $processedCount++;
                        } catch (Exception $polygonException) {
                            Log::error("Failed to validate polygon {$polygonUuid}: " . $polygonException->getMessage());
                            $processedCount++;
                        }

                        $delayedJob->increment('processed_content');
                        $delayedJob->processMessage();

                        if (($polygonIndex + 1) % $this->memoryClearFrequency === 0) {
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
                $continuationJob = new RunSitePolygonsValidationJob($this->delayed_job_id, $remainingPolygons, $this->chunkSize);
                dispatch($continuationJob)->delay(now()->addSeconds(2));

                Log::info("Partial completion: processed {$processedCount}/{$totalPolygons} polygons. Scheduled continuation job with {$this->chunkSize} chunk size.");

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

                $this->sendCompletionEmailSafely($user, $site);
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
            Log::error('Polygon validation error trace: ' . $e->getTraceAsString());

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
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
        } catch (Exception $e) {
            Log::warning('Error during memory cleanup: ' . $e->getMessage());
        }
    }

    /**
     * Send completion email safely - never fails the job
     * Email is queued asynchronously and any errors are logged but not thrown
     *
     * @param $user
     * @param Site $site
     * @return void
     */
    protected function sendCompletionEmailSafely($user, Site $site): void
    {
        try {
            if (! $user || ! $user->email_address) {
                Log::info('Validation completed successfully. No email sent: user or email address not found.');

                return;
            }

            Mail::to($user->email_address)
                ->queue(new PolygonOperationsComplete(
                    $site,
                    'Check',
                    $user,
                    now()
                ));

            Log::info("Validation completed successfully. Completion email queued for: {$user->email_address}");
        } catch (Exception $e) {
            Log::warning('Validation completed successfully. Email notification failed but validation succeeded: ' . $e->getMessage());
        }
    }
}
