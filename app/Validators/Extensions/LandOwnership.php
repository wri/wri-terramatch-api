<?php

namespace App\Validators\Extensions;

class LandOwnership extends Extension
{
    public static $name = 'land_ownership';

    public static $message = [
        'LAND_OWNERSHIP',
        'The {{attribute}} field must contain land ownerships.',
        ['attribute' => ':attribute'],
        'The :attribute field must contain land ownerships.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $landOwnerships = array_unique(array_values(config('data.land_ownerships')));

        return in_array($value, $landOwnerships);
    }
}
