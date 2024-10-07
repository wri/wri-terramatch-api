<?php

namespace App\Jobs;

use App\Helpers\GeometryHelper;
use App\Models\DelayedJob;
use App\Services\PolygonValidationService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class RunSitePolygonsValidationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const STATUS_PENDING = 'pending';
    private const STATUS_FAILED = 'failed';
    private const STATUS_SUCCEEDED = 'succeeded';

    protected $uuid;
    protected $job_uuid;
    

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $uuid)
    {
        $this->uuid = $uuid;
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

            $sitePolygonsUuids = GeometryHelper::getSitePolygonsUuids($this->uuid);
            $validationResults = [];

            foreach ($sitePolygonsUuids as $polygonUuid) {
                $request = new Request(['uuid' => $polygonUuid]);
                
                $validationResults[$polygonUuid] = [
                    'overlapping' => $validationService->validateOverlapping($request),
                    'selfIntersection' => $validationService->checkSelfIntersection($request),
                    'coordinateSystem' => $validationService->validateCoordinateSystem($request),
                    'polygonSize' => $validationService->validatePolygonSize($request),
                    'withinCountry' => $validationService->checkWithinCountry($request),
                    'boundarySegments' => $validationService->checkBoundarySegments($request),
                    'geometryType' => $validationService->getGeometryType($request),
                    'estimatedArea' => $validationService->validateEstimatedArea($request),
                    'dataInDB' => $validationService->validateDataInDB($request),
                ];
            }

            DelayedJob::where('uuid', $this->job_uuid)->update([
                'status' => self::STATUS_SUCCEEDED,
                'payload' => 'Validation completed for all site polygons',
                'updated_at' => now(),
            ]);

        } catch (Exception $e) {
            Log::error('Error in RunSitePolygonsValidationJob: ' . $e->getMessage());
            
            DelayedJob::where('uuid', $this->job_uuid)->update([
                'status' => self::STATUS_FAILED,
                'payload' => json_encode(['error' => $e->getMessage()]),
                'updated_at' => now(),
            ]);
        }
    }

    public function getJobUuid()
    {
        return $this->job_uuid;
    }
}
