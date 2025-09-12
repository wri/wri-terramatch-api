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

class ChunkedPolygonValidationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 300; // 5 minutes per chunk
    public $tries = 3;

    protected $delayed_job_id;
    protected $polygonUuids;
    protected $currentChunkIndex;
    protected $chunkSize;
    protected $totalChunks;

    public function __construct(
        string $delayed_job_id, 
        array $polygonUuids, 
        int $currentChunkIndex = 0,
        int $chunkSize = 50
    ) {
        $this->delayed_job_id = $delayed_job_id;
        $this->polygonUuids = $polygonUuids;
        $this->currentChunkIndex = $currentChunkIndex;
        $this->chunkSize = $chunkSize;
        $this->totalChunks = (int) ceil(count($polygonUuids) / $chunkSize);
    }

    public function handle(PolygonValidationService $validationService, PolygonService $polygonService)
    {
        try {
            $delayedJob = DelayedJobProgress::findOrFail($this->delayed_job_id);
            $user = $delayedJob->creator;
            $metadata = $delayedJob->metadata;

            if (!$user) {
                Log::warning('No creator found for delayed job: ' . $this->delayed_job_id);
            }

            $entityId = $metadata['entity_id'] ?? null;
            if (!$entityId) {
                Log::error('entityId is null, unable to find site');
                throw new Exception('Entity ID is null in delayed job metadata.');
            }

            $site = Site::findOrFail($entityId);

            $chunkStart = $this->currentChunkIndex * $this->chunkSize;
            $chunkEnd = min($chunkStart + $this->chunkSize, count($this->polygonUuids));
            $currentChunk = array_slice($this->polygonUuids, $chunkStart, $chunkEnd - $chunkStart);

            $processedInChunk = 0;
            foreach ($currentChunk as $polygonUuid) {
                $this->validateSinglePolygon($polygonUuid, $validationService, $polygonService);
                $processedInChunk++;

                $delayedJob->increment('processed_content');
                $delayedJob->processMessage();
            }

            $totalProcessed = $delayedJob->processed_content;
            $totalPolygons = count($this->polygonUuids);
            $progress = min(100, round(($totalProcessed / $totalPolygons) * 100, 2));
            
            $delayedJob->update(['progress' => $progress]);
            $delayedJob->save();

            $nextChunkIndex = $this->currentChunkIndex + 1;
            if ($nextChunkIndex < $this->totalChunks) {
                // Schedule next chunk
                $nextJob = new ChunkedPolygonValidationJob(
                    $this->delayed_job_id,
                    $this->polygonUuids,
                    $nextChunkIndex,
                    $this->chunkSize
                );
                
                dispatch($nextJob)->delay(now()->addSeconds(2));
                
                Log::info("Scheduled next chunk " . ($nextChunkIndex + 1) . "/{$this->totalChunks}");
            } else {
                // All chunks completed
                $delayedJob->update([
                    'status' => DelayedJobProgress::STATUS_SUCCEEDED,
                    'payload' => ['message' => 'Validation completed for all site polygons'],
                    'status_code' => Response::HTTP_OK,
                    'progress' => 100,
                ]);

                Log::info("All chunks completed successfully for job: {$this->delayed_job_id}");

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
            }

            $this->clearMemoryAndConnections();

        } catch (Exception $e) {
            Log::error('Error in ChunkedPolygonValidationJob: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
            Log::error('Job data: delayed_job_id=' . $this->delayed_job_id . ', chunk=' . ($this->currentChunkIndex + 1) . '/' . $this->totalChunks);

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
