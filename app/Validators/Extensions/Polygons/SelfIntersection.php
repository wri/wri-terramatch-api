<?php

namespace App\Validators\Extensions\Polygons;

use App\Validators\Extensions\Extension;
use Illuminate\Support\Facades\DB;

class SelfIntersection extends Extension
{
    public static $name = 'polygon_self_intersection';

    public static $message = [
        'key' => 'SELF_INTERSECTION',
        'message' => 'The geometry must not self intersect.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        if (is_string($value)) {
            // assume we have a DB UUID
            return self::uuidValid($value);
        }

        // assume we have GeoJSON
        return self::geoJsonValid($value);
    }

    public static function uuidValid($uuid): bool
    {
        $result = DB::selectOne(
            'SELECT ST_IsSimple(geom) AS is_simple FROM polygon_geometry WHERE uuid = :uuid',
            ['uuid' => $uuid]
        );

        return $result?->is_simple === 1;
    }

    public static function geoJsonValid($geojson): bool
    {
        $result = DB::selectOne(
            'SELECT ST_IsSimple(ST_GeomFromGeoJSON(:geojson)) AS is_simple',
            ['geojson' => json_encode($geojson)]
        );

        return $result?->is_simple === 1;
    }
}
