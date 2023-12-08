<?php

namespace App\Validators\Extensions;

class ArrayArray extends Extension
{
    public static $name = 'array_array';

    public static $message = [
        'ARRAY_ARRAY',
        'The {{attribute}} field must be an array.',
        ['attribute' => ':attribute'],
        'The :attribute field must be an array.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        if (! is_array($value)) {
            return false;
        }
        $expectedKey = 0;
        foreach ($value as $key => $data) {
            if ($key !== $expectedKey) {
                return false;
            }
            $expectedKey += 1;
        }

        return true;
    }
}
