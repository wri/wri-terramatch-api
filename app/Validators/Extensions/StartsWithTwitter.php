<?php

namespace App\Validators\Extensions;

class StartsWithTwitter extends Extension
{
    public static $name = 'starts_with_twitter';

    public static $message = [
        'STARTS_WITH_TWITTER',
        'The {{attribute}} field must be a valid Twitter URL.',
        ['attribute' => ':attribute'],
        'The :attribute field must be a valid Twitter URL.',
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
            strtolower(substr($value, 0, 15)) == 'www.twitter.com' ||
            strtolower(substr($value, 0, 11)) == 'twitter.com';
    }
}
