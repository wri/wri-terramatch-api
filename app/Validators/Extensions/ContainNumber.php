<?php

namespace App\Validators\Extensions;

class ContainNumber extends Extension
{
    public static $name = 'contain_number';

    public static $message = [
        'CONTAIN_NUMBER',
        'The {{attribute}} field must contain a number.',
        ['attribute' => ':attribute'],
        'The :attribute field must contain a number.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $match = preg_match('/[0-9]/', $value);

        return is_int($match) && $match == 1;
    }
}
