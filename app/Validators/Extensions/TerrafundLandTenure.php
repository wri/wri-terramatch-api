<?php

namespace App\Validators\Extensions;

class TerrafundLandTenure extends Extension
{
    public static $name = 'terrafund_land_tenure';

    public static $message = [
        'TERRAFUND_LAND_TENURE',
        'The {{attribute}} field must contain land tenures.',
        ['attribute' => ':attribute'],
        'The :attribute field must contain land tenures.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $restorationMethods = array_unique(array_values(config('data.terrafund.site.land_tenures')));

        return in_array($value, $restorationMethods);
    }
}
