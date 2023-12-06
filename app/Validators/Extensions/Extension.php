<?php

namespace App\Validators\Extensions;

abstract class Extension
{
    public static $name = 'example_extension';

    public static $message = [
        'EXAMPLE_EXTENSION',
        'The {{attribute}} field must be an example.',
        ['attribute' => ':attribute'],
        'The :attribute field must be an example.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        return true;
    }
}
