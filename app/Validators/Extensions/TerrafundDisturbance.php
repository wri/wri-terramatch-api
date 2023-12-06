<?php

namespace App\Validators\Extensions;

class TerrafundDisturbance extends Extension
{
    public static $name = 'terrafund_disturbance';

    public static $message = [
        'TERRAFUND_DISTURBANCE',
        'The {{attribute}} field must contain a Terrafund disturbance.',
        ['attribute' => ':attribute'],
        'The :attribute field must contain Terrafund disturbance.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $restorationMethods = array_unique(array_values(config('data.terrafund.disturbances')));

        return in_array($value, $restorationMethods);
    }
}
