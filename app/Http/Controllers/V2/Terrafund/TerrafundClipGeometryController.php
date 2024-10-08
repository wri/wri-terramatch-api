<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Helpers\CreateVersionPolygonGeometryHelper;
use App\Helpers\GeometryHelper;
use App\Helpers\PolygonGeometryHelper;
use App\Models\V2\Sites\CriteriaSite;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Services\PolygonService;
use App\Services\PythonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class TerrafundClipGeometryController extends TerrafundCreateGeometryController
{
    public function clipOverlappingPolygonsBySite(string $uuid)
    {
        $polygonUuids = GeometryHelper::getSitePolygonsUuids($uuid)->toArray();
        $polygonsClipped = App::make(PolygonService::class)->processClippedPolygons($polygonUuids);
        return response()->json(['updated_polygons' => $polygonsClipped], 200);

    }
    public function clipOverlappingPolygonsOfProjectBySite(string $uuid)
    {
        $sitePolygon = Site::isUuid($uuid)->first();
        $projectId = $sitePolygon->project_id ?? null;
        $polygonUuids = GeometryHelper::getProjectPolygonsUuids($projectId);
        $polygonsClipped = App::make(PolygonService::class)->processClippedPolygons($polygonUuids);
        return response()->json(['updated_polygons' => $polygonsClipped], 200);
    }

    public function clipOverlappingPolygons(Request $request)
    {
        $uuids = $request->input('uuids');
        Log::info('Clipping polygons', ['uuids' => $uuids]);
        if (empty($uuids) || ! is_array($uuids)) {
            return response()->json(['error' => 'Invalid or missing UUIDs'], 400);
        }
        $allPolygonUuids = [];
        $unprocessedPolygons = [];
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
        $processedPolygons = [];
        if (! empty($uniquePolygonUuids)) {
            $processedPolygons = App::make(PolygonService::class)->processClippedPolygons($polygonUuids);
        } else {
            $processedPolygons = null;
        }

        return response()->json([
            'processed' => $processedPolygons,
            'unprocessed' => $unprocessedPolygons,
        ], 200);
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
