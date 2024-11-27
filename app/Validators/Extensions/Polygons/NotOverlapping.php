<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use App\Validators\Extensions\Extension;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotOverlapping extends Extension
{
    public static $name = 'not_overlapping';

    public static $message = [
        'key' => 'OVERLAPPING_POLYGON',
        'message' => 'The geometry must not overlap with other project geometry',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        return self::getIntersectionData($value)['valid'];
    }

    public static function getIntersectionData(string $polygonUuid): array
    {
        $sitePolygon = SitePolygon::forPolygonGeometry($polygonUuid)->first();
        if ($sitePolygon === null) {
            return [
                'valid' => false,
                'error' => 'Site polygon not found for the given polygon ID',
                'status' => 404,
            ];
        }
        $relatedPolyIds = $sitePolygon->project->sitePolygons()
            ->where('poly_id', '!=', $polygonUuid)
            ->pluck('poly_id');
        $bboxFilteredPolyIds = PolygonGeometry::join('site_polygon', 'polygon_geometry.uuid', '=', 'site_polygon.poly_id')
        ->whereIn('polygon_geometry.uuid', $relatedPolyIds)
        ->whereRaw('ST_Intersects(ST_Envelope(polygon_geometry.geom), (SELECT ST_Envelope(geom) FROM polygon_geometry WHERE uuid = ?))', [$polygonUuid])
        ->pluck('polygon_geometry.uuid');

        $intersects = PolygonGeometry::join('site_polygon', 'polygon_geometry.uuid', '=', 'site_polygon.poly_id')
        ->whereIn('polygon_geometry.uuid', $bboxFilteredPolyIds)
        ->select([
            'polygon_geometry.uuid',
            'site_polygon.poly_name',
            DB::raw('ST_Intersects(polygon_geometry.geom, (SELECT geom FROM polygon_geometry WHERE uuid = ?)) as intersects'),
            DB::raw('ST_Area(ST_Intersection(polygon_geometry.geom, (SELECT geom FROM polygon_geometry WHERE uuid = ?))) as intersection_area'),
            DB::raw('ST_Area(polygon_geometry.geom) as area'),
        ])
        ->addBinding($polygonUuid, 'select')
        ->addBinding($polygonUuid, 'select')
        ->get();

        $mainPolygonArea = PolygonGeometry::where('uuid', $polygonUuid)
            ->value(DB::raw('ST_Area(geom)'));

        $extra_info = [];
        foreach ($intersects as $intersect) {
            if ($intersect->intersects) {
                $minArea = min($mainPolygonArea, $intersect->area);
                $percentage = $minArea > 0 ? round(($intersect->intersection_area / $minArea) * 100, 2) : 100;
                $extra_info[] = [
                    'poly_uuid' => $intersect->uuid,
                    'poly_name' => $intersect->poly_name,
                    'percentage' => $percentage,
                    'intersectSmaller' => ($intersect->area < $mainPolygonArea),
                ];
            }
        }

        return [
            'valid' => ! $intersects->contains('intersects', 1),
            'uuid' => $polygonUuid,
            'project_id' => $sitePolygon->project_id,
            'extra_info' => $extra_info,
        ];
    }

    public static function checkFeatureIntersections($geojsonFeatures): array
    {
        if (! is_array($geojsonFeatures) || empty($geojsonFeatures)) {
            return ['valid' => false, 'error' => 'Invalid or empty GeoJSON features array'];
        }

        $intersectingPositions = [];
        $count = count($geojsonFeatures);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $geom1 = json_encode($geojsonFeatures[$i]['geometry']);
                $geom2 = json_encode($geojsonFeatures[$j]['geometry']);

                $result = DB::select(
                    'SELECT ST_Intersects(
                        ST_GeomFromGeoJSON(?),
                        ST_GeomFromGeoJSON(?)
                    ) as intersects',
                    [$geom1, $geom2]
                );
                if ($result[0]->intersects) {
                    if (! in_array($i, $intersectingPositions)) {
                        $intersectingPositions[] = $i;
                    }
                    if (! in_array($j, $intersectingPositions)) {
                        $intersectingPositions[] = $j;
                    }
                }
            }
        }

        return [
            'valid' => empty($intersectingPositions),
            'intersections' => $intersectingPositions,
        ];
    }

    public static function doesNotOverlap($geojson, $siteId): array
    {
        $sitePolygon = SitePolygon::where('site_id', $siteId)->first();
        if ($sitePolygon == null) {
            return ['valid' => true, 'error' => 'Site polygon not found for the given site ID', 'status' => 404];
        }

        $relatedPolyIds = $sitePolygon->project->sitePolygons()->pluck('poly_id');
        $intersects = PolygonGeometry::whereIn('uuid', $relatedPolyIds)
            ->selectRaw(
                'ST_Intersects(
                geom, 
                ST_GeomFromGeoJSON(?)
            ) as intersects',
                [$geojson]
            )
            ->get()
            ->pluck('intersects');

        return [
            'valid' => ! in_array(1, $intersects->toArray()),
            'site_id' => $siteId,
            'project_id' => $sitePolygon->project->id,
        ];
    }
}
