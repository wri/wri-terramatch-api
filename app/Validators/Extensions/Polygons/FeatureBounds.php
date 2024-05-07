<?php

namespace App\Validators\Extensions\Polygons;

use App\Validators\Extensions\Extension;

class FeatureBounds extends Extension
{
    public static $name = 'polygon_feature_bounds';

    public static $message = [
        'FEATURE_BOUNDS',
        'The {{attribute}} field must have valid feature polygon bounds.',
        ['attribute' => ':attribute'],
        'The :attribute field must have valid feature polygon bounds.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $type = data_get($value, 'geometry.type');
        if ($type === 'Polygon') {
            return self::hasValidPolygonBounds(data_get($value, 'geometry.coordinates'));
        } elseif ($type === 'MultiPolygon') {
            foreach (data_get($value, 'geometry.coordinates') as $coordinates) {
                if (! self::hasValidPolygonBounds($coordinates)) {
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
