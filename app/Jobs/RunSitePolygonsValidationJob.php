<?php

namespace App\Jobs;

use App\Models\DelayedJob;
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
use Illuminate\Support\Str;

class RunSitePolygonsValidationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 0;
    
    private const STATUS_PENDING = 'pending';
    private const STATUS_FAILED = 'failed';
    private const STATUS_SUCCEEDED = 'succeeded';

    protected $uuid;

    protected $job_uuid;

    protected $sitePolygonsUuids;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $sitePolygonsUuids)
    {
        $this->sitePolygonsUuids = $sitePolygonsUuids;
        $this->job_uuid = Str::uuid()->toString();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PolygonValidationService $validationService)
    {
        try {
            DelayedJob::create([
                'uuid' => $this->job_uuid,
                'status' => self::STATUS_PENDING,
                'created_at' => now(),
            ]);
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
            }

            DelayedJob::where('uuid', $this->job_uuid)->update([
                'status' => self::STATUS_SUCCEEDED,
                'payload' => 'Validation completed for all site polygons',
                'updated_at' => now(),
                'status_code' => Response::HTTP_OK,
            ]);

        } catch (Exception $e) {
            Log::error('Error in RunSitePolygonsValidationJob: ' . $e->getMessage());

            DelayedJob::where('uuid', $this->job_uuid)->update([
                'status' => self::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'updated_at' => now(),
                'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);
        }
    }

    public function getJobUuid()
    {
        return $this->job_uuid;
    }
}
