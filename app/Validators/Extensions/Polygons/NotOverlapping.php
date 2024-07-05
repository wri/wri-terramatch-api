<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Models\V2\Sites\SitePolygon;
use App\Validators\Extensions\Extension;
use Illuminate\Support\Facades\DB;

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
        if ($sitePolygon == null) {
            return ['valid' => false, 'error' => 'Site polygon not found for the given polygon ID', 'status' => 404];
        }

        $relatedPolyIds = $sitePolygon->project->sitePolygons()->whereNot('poly_id', $polygonUuid)->pluck('poly_id');
        $intersects = PolygonGeometry::whereIn('uuid', $relatedPolyIds)
            ->selectRaw(
                'ST_Intersects(
                    geom, 
                    (SELECT geom FROM polygon_geometry WHERE uuid = ?)
                ) as intersects',
                [$polygonUuid]
            )
            ->get()
            ->pluck('intersects');

        return [
            'valid' => ! in_array(1, $intersects->toArray()),
            'uuid' => $polygonUuid,
            'project_id' => $sitePolygon->project->id,
        ];
    }

    public static function checkFeatureIntersections($geojsonFeatures): array
    {
        if (!is_array($geojsonFeatures) || empty($geojsonFeatures)) {
            return ['valid' => false, 'error' => 'Invalid or empty GeoJSON features array'];
        }
    
        $intersections = [];
        $count = count($geojsonFeatures);
    
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $geom1 = json_encode($geojsonFeatures[$i]['geometry']);
                $geom2 = json_encode($geojsonFeatures[$j]['geometry']);
    
                $result = DB::select(
                    "SELECT ST_Intersects(
                        ST_GeomFromGeoJSON(?),
                        ST_GeomFromGeoJSON(?)
                    ) as intersects",
                    [$geom1, $geom2]
                );
    
                if ($result[0]->intersects) {
                    $intersections[] = [$i, $j];
                }
            }
        }
    
        return [
            'valid' => empty($intersections),
            'intersections' => $intersections
        ];
    }
    public static function doesNotOverlap($geojson, $siteId): array
    {
        $sitePolygon = SitePolygon::where('site_id', $siteId)->first();
        if ($sitePolygon == null) {
            return ['valid' => false, 'error' => 'Site polygon not found for the given site ID', 'status' => 404];
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
