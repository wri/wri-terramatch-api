<?php

namespace App\Validators\Extensions;

class GeoJson extends Extension
{
    public static $name = 'geo_json';

    public static $message = [
        'GEO_JSON',
        'The {{attribute}} field must be valid GeoJSON.',
        ['attribute' => ':attribute'],
        'The :attribute field must be valid GeoJSON.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $geojson = json_decode($value);

        return
            is_object($geojson) &&
            property_exists($geojson, 'type') &&
            is_string($geojson->type) &&
            strlen($geojson->type) > 0;
    }
}
