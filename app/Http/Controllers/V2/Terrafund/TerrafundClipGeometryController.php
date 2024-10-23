<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Helpers\GeometryHelper;
use App\Jobs\FixPolygonOverlapJob;
use App\Models\V2\Sites\CriteriaSite;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Services\PolygonService;
use Illuminate\Http\Request;
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
        $polygonUuids = GeometryHelper::getSitePolygonsUuids($uuid)->toArray();
        $job = new FixPolygonOverlapJob($polygonUuids, $user->id);
        $jobUUID = $job->getJobUuid();
        dispatch($job);

        return response()->json(['job_uuid' => $jobUUID], 200);

    }

    public function clipOverlappingPolygonsOfProjectBySite(string $uuid)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');
        $user = Auth::user();
        $sitePolygon = Site::isUuid($uuid)->first();
        $projectId = $sitePolygon->project_id ?? null;
        $polygonUuids = GeometryHelper::getProjectPolygonsUuids($projectId);
        $job = new FixPolygonOverlapJob($polygonUuids, $user->id);
        $jobUUID = $job->getJobUuid();
        dispatch($job);

        return response()->json(['job_uuid' => $jobUUID], 200);
    }

    public function clipOverlappingPolygons(Request $request)
    {
        ini_set('max_execution_time', self::MAX_EXECUTION_TIME);
        ini_set('memory_limit', '-1');
        $uuids = $request->input('uuids');
        Log::info('Clipping polygons', ['uuids' => $uuids]);
        $jobUUID = null;
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
        if (! empty($uniquePolygonUuids)) {
            $user = Auth::user();
            $job = new FixPolygonOverlapJob($polygonUuids, $user->id);
            $jobUUID = $job->getJobUuid();
            dispatch($job);
        }

        return response()->json(['job_uuid' => $jobUUID,], 200);
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
