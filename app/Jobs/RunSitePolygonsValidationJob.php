<?php

namespace App\Jobs;

use App\Models\DelayedJob;
use App\Models\DelayedJobProgress;
use App\Services\PolygonValidationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunSitePolygonsValidationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;

    protected $uuid;

    protected $delayed_job_id;

    protected $sitePolygonsUuids;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $delayed_job_id, array $sitePolygonsUuids)
    {
        $this->sitePolygonsUuids = $sitePolygonsUuids;
        $this->delayed_job_id = $delayed_job_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PolygonValidationService $validationService)
    {
        try {
            $delayedJob = DelayedJobProgress::findOrFail($this->delayed_job_id);
            foreach ($this->sitePolygonsUuids as $polygonUuid) {
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

                $delayedJob->increment('processed_content');
                $delayedJob->processMessage();
                $delayedJob->save();
            }

            $delayedJob->update([
                'status' => DelayedJobProgress::STATUS_SUCCEEDED,
                'payload' => ['message' => 'Validation completed for all site polygons'],
                'status_code' => Response::HTTP_OK,
                'progress' => 100,
            ]);

        } catch (Exception $e) {
            Log::error('Error in RunSitePolygonsValidationJob: ' . $e->getMessage());

            DelayedJob::where('id', $this->delayed_job_id)->update([
                'status' => DelayedJob::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }
}
