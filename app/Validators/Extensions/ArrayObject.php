<?php

namespace App\Validators\Extensions;

class ArrayObject extends Extension
{
    public static $name = 'array_object';

    public static $message = [
        'ARRAY_OBJECT',
        'The {{attribute}} field must be an object.',
        ['attribute' => ':attribute'],
        'The :attribute field must be an object.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        if (! is_array($value)) {
            return false;
        }
        foreach ($value as $key => $data) {
            if (! is_string($key)) {
                return false;
            }
        }

        return true;
    }
}
