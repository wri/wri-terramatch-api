<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Helpers\GeometryHelper;
use App\Http\Resources\DelayedJobResource;
use App\Jobs\FixPolygonOverlapJob;
use App\Models\DelayedJob;
use App\Models\DelayedJobProgress;
use App\Models\V2\Sites\CriteriaSite;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Services\PolygonService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TerrafundClipGeometryController extends TerrafundCreateGeometryController
{
    private const MAX_EXECUTION_TIME = 340;

    public function clipOverlappingPolygonsBySite(string $uuid)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');
        $user = Auth::user();
        $site = Site::isUuid($uuid)->first();
        $delayedJob = DelayedJobProgress::create([
          'processed_content' => 0,
          'created_by' => $user->id,
          'metadata' => [
              'entity_id' => $site->id,
              'entity_type' => get_class($site),
              'entity_name' => $site->name,
          ],
          'is_acknowledged' => false,
          'name' => 'Polygon Fix',
      ]);
        $polygonUuids = GeometryHelper::getSitePolygonsUuids($uuid)->toArray();
        $job = new FixPolygonOverlapJob($delayedJob->id, $polygonUuids, $user->id);
        dispatch($job);

        return new DelayedJobResource($delayedJob);

    }

    public function clipOverlappingPolygonsOfProjectBySite(string $uuid)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');
        $user = Auth::user();
        $site = Site::isUuid($uuid)->first();
        $projectId = $site->project_id ?? null;

        if (! $projectId) {
            return response()->json(['error' => 'Project not found for the given site UUID.'], 404);
        }

        $polygonUuids = GeometryHelper::getProjectPolygonsUuids($projectId);

        if (empty($polygonUuids)) {
            return response()->json(['message' => 'No polygons found for the project.'], 204);
        }

        $delayedJob = DelayedJobProgress::create([
          'processed_content' => 0,
          'metadata' => [
            'entity_id' => $site->id,
            'entity_type' => get_class($site),
            'entity_name' => $site->name,
          ],
          'created_by' => $user->id,
          'is_acknowledged' => false,
          'name' => 'Polygon Fix',
        ]);
        $allPolygonUuids = [];
        foreach ($polygonUuids as $uuid) {
            $polygonOverlappingExtraInfo = CriteriaSite::forCriteria(PolygonService::OVERLAPPING_CRITERIA_ID)
                ->where('polygon_id', $uuid)
                ->first()
                ->extra_info ?? null;

            if ($polygonOverlappingExtraInfo) {
                $decodedInfo = json_decode($polygonOverlappingExtraInfo, true);
                $polygonUuidsOverlapping = array_map(function ($item) {
                    return $item['poly_uuid'] ?? null;
                }, $decodedInfo);
                $polygonUuidsFiltered = array_filter($polygonUuidsOverlapping);

                array_unshift($polygonUuidsFiltered, $uuid);
                $allPolygonUuids = array_merge($allPolygonUuids, $polygonUuidsFiltered);
            }
        }

        $uniquePolygonUuids = array_unique($allPolygonUuids);

        if (empty($uniquePolygonUuids)) {
            DelayedJob::where('uuid', $delayedJob->uuid)->update([
              'status' => DelayedJob::STATUS_FAILED,
              'payload' => json_encode(['error' => 'No overlapping polygons found for the project.']),
              'status_code' => Response::HTTP_INTERNAL_SERVER_ERROR,
            ]);

            return response()->json(['message' => 'No overlapping polygons found for the project.'], 204);
        }


        $job = new FixPolygonOverlapJob($delayedJob->id, $uniquePolygonUuids, $user->id);
        dispatch($job);

        return new DelayedJobResource($delayedJob);
    }

    public function clipOverlappingPolygons(Request $request)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');
        $uuids = $request->input('uuids');
        $uuid = $request->input('entity_uuid');
        $type = $request->input('entity_type');
        if ($type === 'sites') {
            $entity = Site::where('uuid', $uuid)->firstOrFail();
        }
        Log::info('Clipping polygons', ['uuids' => $uuids]);
        if (empty($uuids) || ! is_array($uuids)) {
            return response()->json(['error' => 'Invalid or missing UUIDs'], 400);
        }
        $allPolygonUuids = [];
        foreach ($uuids as $uuid) {
            $polygonOverlappingExtraInfo = CriteriaSite::forCriteria(PolygonService::OVERLAPPING_CRITERIA_ID)
                ->where('polygon_id', $uuid)
                ->first()
                ->extra_info ?? null;

            if (! $polygonOverlappingExtraInfo) {
                $sitePolygon = SitePolygon::where('poly_id', $uuid)->active()->first();
                if ($sitePolygon) {
                    $unprocessedPolygons[] = [
                      'uuid' => $uuid,
                      'poly_name' => $sitePolygon->poly_name ?? 'Unnamed Polygon',
                    ];
                } else {
                    $unprocessedPolygons[] = $uuid;
                }

                continue;
            }
            $decodedInfo = json_decode($polygonOverlappingExtraInfo, true);
            $polygonUuidsOverlapping = array_map(function ($item) {
                return $item['poly_uuid'] ?? null;
            }, $decodedInfo);
            $polygonUuids = array_filter($polygonUuidsOverlapping);
            array_unshift($polygonUuids, $uuid);
            $allPolygonUuids = array_merge($allPolygonUuids, $polygonUuids);
        }
        $uniquePolygonUuids = array_unique($allPolygonUuids);
        $delayedJob = null;
        if (! empty($uniquePolygonUuids)) {
            $user = Auth::user();
            $delayedJob = DelayedJobProgress::create([
                'processed_content' => 0,
                'metadata' => [
                  'entity_id' => $entity->id,
                  'entity_type' => get_class($entity),
                  'entity_name' => $entity->name,
                ],
                'created_by' => $user->id,
                'is_acknowledged' => false,
                'name' => 'Polygon Fix',
            ]);
            $job = new FixPolygonOverlapJob($delayedJob->id, $polygonUuids, $user->id);
            dispatch($job);
        }

        if ($delayedJob) {
            return new DelayedJobResource($delayedJob);
        } else {
            return response()->json(['message' => 'No overlapping polygons found or processed.'], 204);
        }
    }

    public function clipOverlappingPolygon(string $uuid)
    {
        $polygonOverlappingExtraInfo = CriteriaSite::forCriteria(PolygonService::OVERLAPPING_CRITERIA_ID)
          ->where('polygon_id', $uuid)
          ->first()
          ->extra_info ?? null;

        if (! $polygonOverlappingExtraInfo) {
            return response()->json(['error' => 'Need to run checks or there is no overlapping error'], 400);
        }
        $decodedInfo = json_decode($polygonOverlappingExtraInfo, true);

        $polygonUuidsOverlapping = array_map(function ($item) {
            return $item['poly_uuid'] ?? null;
        }, $decodedInfo);

        $polygonUuids = array_filter($polygonUuidsOverlapping);

        array_unshift($polygonUuids, $uuid);
        $polygonsClipped = App::make(PolygonService::class)->processClippedPolygons($polygonUuids);

        return response()->json(['updated_polygons' => $polygonsClipped], 200);
    }
}
