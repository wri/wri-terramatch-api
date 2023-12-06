<?php

namespace App\Validators\Extensions;

class StrictFloat extends Extension
{
    public static $name = 'strict_float';

    public static $message = [
        'STRICT_FLOAT',
        'The {{attribute}} field may not have more than 2 decimal places.',
        ['attribute' => ':attribute'],
        'The :attribute field may not have more than 2 decimal places.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        if (is_int($value)) {
            return true;
        } elseif (is_float($value)) {
            list($prefix, $suffix) = explode('.', strval($value), 2);

            return
                ctype_digit($prefix) && strlen($prefix) >= 1 &&
                ctype_digit($suffix) && in_array(strlen($suffix), [1, 2]);
        } else {
            return false;
        }
    }
}
