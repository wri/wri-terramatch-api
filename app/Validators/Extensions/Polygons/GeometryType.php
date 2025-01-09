<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Validators\Extensions\Extension;
use Illuminate\Support\Facades\DB;

class GeometryType extends Extension
{
    public static $name = 'geometry_type';

    public static $message = [
        'key' => 'GEOMETRY_TYPE',
        'message' => 'All geometries in the collection must be either all points OR all polygons/multipolygons',
    ];

    public const VALID_TYPE_POLYGON = 'POLYGON';
    public const VALID_TYPE_MULTIPOLYGON = 'MULTIPOLYGON';
    public const VALID_TYPE_POINT = 'POINT';

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        if (! is_array($value)) {
            return false;
        }

        $types = [];
        foreach ($value as $feature) {
            $type = self::getGeometryType($feature);
            if (! in_array($type, [
                self::VALID_TYPE_POLYGON,
                self::VALID_TYPE_MULTIPOLYGON,
                self::VALID_TYPE_POINT,
            ])) {
                return false;
            }

            $types[] = $type;
        }

        $hasPoints = in_array(self::VALID_TYPE_POINT, $types);
        $hasPolygons = in_array(self::VALID_TYPE_POLYGON, $types) ||
                      in_array(self::VALID_TYPE_MULTIPOLYGON, $types);

        return ! ($hasPoints && $hasPolygons);
    }

    public static function uuidValid($uuid): bool
    {
        $geometryType = PolygonGeometry::getGeometryType($uuid);

        return $geometryType === self::VALID_TYPE_POLYGON || $geometryType === self::VALID_TYPE_MULTIPOLYGON;
    }

    private static function getGeometryType($feature): string
    {
        if (is_string($feature)) {
            return PolygonGeometry::getGeometryType($feature);
        }

        $result = DB::selectOne(
            'SELECT ST_GeometryType(ST_GeomFromGeoJSON(:geojson)) AS geometry_type',
            ['geojson' => json_encode($feature)]
        );

        return $result->geometry_type;
    }
}
