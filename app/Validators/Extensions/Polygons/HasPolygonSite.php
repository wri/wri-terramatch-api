<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\Sites\SitePolygon;
use App\Validators\Extensions\Extension;

class HasPolygonSite extends Extension
{
    public static $name = 'has_polygon_site';

    public static $message = [
        'HAS_POLYGON_SITE',
        'The {{attribute}} field must represent a polygon with an attached site',
        ['attribute' => ':attribute'],
        'The :attribute field must represent a polygon with an attached site'
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        return SitePolygon::where('poly_id', $value)->exists();
    }
}
