<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\PolygonGeometry;
use App\Validators\Extensions\Extension;

class FeatureBounds extends Extension
{
    public static $name = 'polygon_feature_bounds';

    public static $message = [
        'key' => 'COORDINATE_SYSTEM',
        'message' => 'The coordinates must have valid lat/lng values.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        if (is_string($value)) {
            // assume we have a DB UUID
            return self::uuidValid($value);
        }

        // assume we have a GeoJSON
        return self::geoJsonValid($value);
    }

    public static function uuidValid($uuid): bool
    {
        return self::geoJsonValid(PolygonGeometry::getGeoJson($uuid));
    }

    public static function geoJsonValid($geojson): bool
    {
        $type = data_get($geojson, 'geometry.type');
        if ($type === 'Polygon') {
            return self::hasValidPolygonBounds(data_get($geojson, 'geometry.coordinates.0'));
        } elseif ($type === 'MultiPolygon') {
            foreach (data_get($geojson, 'geometry.coordinates') as $coordinates) {
                if (! self::hasValidPolygonBounds($coordinates[0])) {
                    return false;
                }
            }
        }

        return true;
    }

    private static function hasValidPolygonBounds($coordinates): bool
    {
        foreach ($coordinates as $coordinate) {
            $latitude = $coordinate[1];
            $longitude = $coordinate[0];
            if ($latitude < -90 || $latitude > 90) {
                return false;
            }
            if ($longitude < -180 || $longitude > 180) {
                return false;
            }
        }

        return true;
    }
}
