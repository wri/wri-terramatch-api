<?php

namespace App\Validators\Extensions\Polygons;

use App\Models\V2\Sites\SitePolygon;
use App\Validators\Extensions\Extension;

class HasPolygonSite extends Extension
{
    public static $name = 'has_polygon_site';

    public static $message = [
        'key' => 'HAS_POLYGON_SITE',
        'message' => 'The geometry must have an attached site',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        return SitePolygon::where('poly_id', $value)->exists();
    }
}
