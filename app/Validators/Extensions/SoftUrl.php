<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SoftUrl extends Extension
{
    public static $name = 'soft_url';

    public static $message = [
        'SOFT_URL',
        'The {{attribute}} field must be a valid URL.',
        ['attribute' => ':attribute'],
        'The :attribute field must be a valid URL.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $startsWithHttp = strtolower(substr($value, 0, 7)) == 'http://';
        $startsWithHttps = strtolower(substr($value, 0, 8)) == 'https://';
        if (! $startsWithHttp && ! $startsWithHttps) {
            $value = 'http://' . $value;
        }
        $isValidUrl = Validator::make(['domain' => $value], ['domain' => 'url'])->fails() !== true;
        $parts = $isValidUrl ? explode('.', $value) : [];
        $containsTopLevelDomain = count($parts) > 1 && $value[-1] !== '.';
        $doesNotContainConsecutiveFullstops = ! Str::contains($value, '..');

        return $isValidUrl && $containsTopLevelDomain && $doesNotContainConsecutiveFullstops;
    }
}
