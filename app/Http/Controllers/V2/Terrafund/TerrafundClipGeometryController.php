<?php

namespace App\Http\Controllers\V2\Terrafund;

use App\Helpers\CreateVersionPolygonGeometryHelper;
use App\Helpers\GeometryHelper;
use App\Helpers\PolygonGeometryHelper;
use App\Models\V2\Sites\CriteriaSite;
use App\Services\PolygonService;
use App\Services\PythonService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class TerrafundClipGeometryController extends TerrafundCreateGeometryController
{
    public function clipOverlappingPolygonsBySite(string $uuid)
    {
        $polygonUuids = GeometryHelper::getSitePolygonsUuids($uuid)->toArray();

        return $this->processClippedPolygons($polygonUuids);
    }

    public function clipOverlappingPolygons(string $uuid)
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

                    if (($key = array_search($poly_id, $polygonUuids)) !== false) {
                        unset($polygonUuids[$key]);
                    }
                }
            }
            $polygonUuids = array_values($polygonUuids);
            $newPolygonUuids = array_merge($uuids, $polygonUuids);
        } else {
            Log::error('Error clipping polygons', ['clippedPolygons' => $clippedPolygons]);
        }

        if (! empty($uuids)) {
            foreach ($newPolygonUuids as $polygonUuid) {
                $this->runValidationPolygon($polygonUuid);
            }
        }

        $updatedPolygons = PolygonGeometryHelper::getPolygonsProjection($uuids, ['poly_id', 'poly_name']);

        return response()->json(['updated_polygons' => $updatedPolygons]);
    }
}
