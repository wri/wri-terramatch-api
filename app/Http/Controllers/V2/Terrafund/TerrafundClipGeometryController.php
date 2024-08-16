<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Helpers\CreateVersionPolygonGeometryHelper;
use App\Helpers\GeometryHelper;
use App\Helpers\PolygonGeometryHelper;
use App\Http\Controllers\Controller;
use App\Models\V2\Sites\CriteriaSite;
use App\Services\PolygonService;
use App\Services\PythonService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class TerrafundClipGeometryController extends Controller
{
    public function clipOverlappingPolygonsBySite(string $uuid)
    {
        $polygonUuids = GeometryHelper::getSitePolygonsUuids($uuid)->toArray();

        return $this->processClippedPolygons($polygonUuids);
    }

    public function clipOverlappingPolygons(string $uuid)
    {
        $polygonOverlappingExtraInfo = CriteriaSite::polygonProjection($uuid, PolygonService::OVERLAPPING_CRITERIA_ID)
            ->value('extra_info');
        if (! $polygonOverlappingExtraInfo) {
            return response()->json(['error' => 'Need to run checks.'], 400);
        }
        $decodedInfo = json_decode($polygonOverlappingExtraInfo, true);

        $polygonUuidsOverlapping = array_map(function ($item) {
            return $item['poly_uuid'] ?? null;
        }, $decodedInfo);

        $polygonUuids = array_filter($polygonUuidsOverlapping);

        array_unshift($polygonUuids, $uuid);

        return $this->processClippedPolygons($polygonUuids);
    }

    private function processClippedPolygons(array $polygonUuids)
    {
        $geojson = GeometryHelper::getPolygonsGeojson($polygonUuids);

        $clippedPolygons = App::make(PythonService::class)->clipPolygons($geojson);
        $uuids = [];

        if (isset($clippedPolygons['type']) && $clippedPolygons['type'] === 'FeatureCollection' && isset($clippedPolygons['features'])) {
            foreach ($clippedPolygons['features'] as $feature) {
                if (isset($feature['properties']['poly_id'])) {
                    $poly_id = $feature['properties']['poly_id'];
                    $result = CreateVersionPolygonGeometryHelper::createVersionPolygonGeometry($poly_id, json_encode(['geometry' => $feature]));

                    if (isset($result->original['uuid'])) {
                        $uuids[] = $result->original['uuid'];
                    }
                }
            }
        } else {
            Log::error('Error clipping polygons', ['clippedPolygons' => $clippedPolygons]);
        }

        $updatedPolygons = PolygonGeometryHelper::getPolygonsProjection($uuids, ['poly_id', 'poly_name']);

        return response()->json(['updated_polygons' => $updatedPolygons]);
    }
}
