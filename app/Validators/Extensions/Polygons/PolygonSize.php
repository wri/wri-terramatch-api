<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Validators\Extensions\Extension;
use Illuminate\Support\Facades\DB;

class PolygonSize extends Extension
{
    public static $name = 'polygon_size';

    public static $message = [
        'key' => 'SIZE_LIMIT',
        'message' => 'The geometry must not be larger than ' . self::SIZE_LIMIT . 'square kilometers',
    ];

    public const SIZE_LIMIT = 10000000;

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
        $areaSqMeters = self::calculateSqMeters(PolygonGeometry::isUuid($uuid)->first()->db_geometry);

        return $areaSqMeters <= self::SIZE_LIMIT;
    }

    public static function geoJsonValid($geojson): bool
    {
        $areaSqMeters = self::calculateSqMeters(DB::selectOne(
            'SELECT 
                ST_Area(params.geom) AS area, 
                ST_Y(ST_Centroid(params.geom)) AS latitude
            FROM (
                SELECT ST_GeomFromGeoJSON(:geojson) AS geom
            ) as params',
            ['geojson' => json_encode($geojson)]
        ));

        return $areaSqMeters <= self::SIZE_LIMIT;
    }

    public static function calculateSqMeters($dbGeometry): float
    {
        $areaSqDegrees = $dbGeometry->area;
        $latitude = $dbGeometry->latitude;

        return $areaSqDegrees * pow(111320 * cos(deg2rad($latitude)), 2);
    }
}
