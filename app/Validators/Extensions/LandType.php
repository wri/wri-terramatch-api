<?php

namespace App\Validators\Extensions;

class LandType extends Extension
{
    public static $name = 'land_type';

    public static $message = [
        'LAND_TYPE',
        'The {{attribute}} field must contain land types.',
        ['attribute' => ':attribute'],
        'The :attribute field must contain land types.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $landTypes = array_unique(array_values(config('data.land_types')));

        return in_array($value, $landTypes);
    }
}
