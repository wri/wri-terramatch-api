<?php

namespace App\Jobs;

use App\Models\DelayedJob;
use App\Services\PolygonValidationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
            $delayedJob = DelayedJob::findOrFail($this->delayed_job_id);
            foreach ($this->sitePolygonsUuids as $polygonUuid) {
                Log::info("Before validation validateOverlappings");
                Log::info(now());
                $validationService->validateOverlappings($polygonUuid);
                Log::info("Before validation checkSelfIntersections");
                Log::info(now());
                $validationService->checkSelfIntersections($polygonUuid);
                Log::info("Before validation validateCoordinateSystems");
                Log::info(now());
                $validationService->validateCoordinateSystems($polygonUuid);
                Log::info("Before validation validatePolygonSizes");
                Log::info(now());
                $validationService->validatePolygonSizes($polygonUuid);
                Log::info("Before validation checkWithinCountrys");
                Log::info(now());
                $validationService->checkWithinCountrys($polygonUuid);
                Log::info("Before validation checkBoundarySegment");
                Log::info(now());
                $validationService->checkBoundarySegment($polygonUuid);
                Log::info("Before validation getGeometryTypes");
                Log::info(now());
                $validationService->getGeometryTypes($polygonUuid);
                Log::info("Before validation validateEstimatedAreas");
                Log::info(now());
                $validationService->validateEstimatedAreas($polygonUuid);
                Log::info("Before validation validateDataInDBs");
                Log::info(now());
                $validationService->validateDataInDBs($polygonUuid);
            }

            $delayedJob->update([
                'status' => DelayedJob::STATUS_SUCCEEDED,
                'payload' => ['message' => 'Validation completed for all site polygons'],
                'status_code' => Response::HTTP_OK,
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
