<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Validators\Extensions\Extension;
use Illuminate\Support\Facades\DB;

class PolygonType extends Extension
{
    public static $name = 'polygon_type';

    public static $message = [
        'POLYGON_TYPE',
        'The {{attribute}} field must not represent geojson that is not polygon geometry',
        ['attribute' => ':attribute'],
        'The :attribute field must not represent geojson that is not polygon geometry',
    ];

    public const VALID_TYPE = 'POLYGON';

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
        $geometryType = PolygonGeometry::getGeometryType($uuid);

        return $geometryType === self::VALID_TYPE;
    }

    public static function geoJsonValid($geojson): bool
    {
        $result = DB::selectOne(
            'SELECT ST_GeometryType(ST_GeomFromGeoJSON(:geojson)) AS geometry_type',
            ['geojson' => json_encode($geojson)]
        );

        return $result->geometry_type === self::VALID_TYPE;
    }
}
