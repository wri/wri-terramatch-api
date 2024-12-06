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
                $validationService->validateOverlappings($polygonUuid);
                $validationService->checkSelfIntersections($polygonUuid);
                $validationService->validateCoordinateSystems($polygonUuid);
                $validationService->validatePolygonSizes($polygonUuid);
                $validationService->checkWithinCountrys($polygonUuid);
                $validationService->checkBoundarySegment($polygonUuid);
                $validationService->getGeometryTypes($polygonUuid);
                $validationService->validateEstimatedAreas($polygonUuid);
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
