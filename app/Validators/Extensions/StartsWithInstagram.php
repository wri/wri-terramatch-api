<?php

namespace App\Validators\Extensions;

class StartsWithInstagram extends Extension
{
    public static $name = 'starts_with_instagram';

    public static $message = [
        'STARTS_WITH_INSTAGRAM',
        'The {{attribute}} field must be a valid Instagram URL.',
        ['attribute' => ':attribute'],
        'The :attribute field must be a valid Instagram URL.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $startsWithHttp = strtolower(substr($value, 0, 7)) == 'http://';
        $startsWithHttps = strtolower(substr($value, 0, 8)) == 'https://';
        if ($startsWithHttp) {
            $value = substr($value, 7);
        } elseif ($startsWithHttps) {
            $value = substr($value, 8);
        }

        return
            strtolower(substr($value, 0, 17)) == 'www.instagram.com' ||
            strtolower(substr($value, 0, 13)) == 'instagram.com';
    }
}
