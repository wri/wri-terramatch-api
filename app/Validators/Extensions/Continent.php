<?php

namespace App\Validators\Extensions;

class Continent extends Extension
{
    public static $name = 'continent';

    public static $message = [
        'CONTINENT',
        'The {{attribute}} field must be a continent.',
        ['attribute' => ':attribute'],
        'The :attribute field must be a continent.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $continents = array_unique(array_values(config('data.continents')));

        return in_array($value, $continents);
    }
}
