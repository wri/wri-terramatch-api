<?php

namespace App\Validators\Extensions;

class SoftUrl extends Extension
{
    public static $name = "soft_url";
    public static $message = [
        "SOFT_URL",
        "The {{attribute}} field must be a valid URL.",
        ["attribute" => ":attribute"],
        "The :attribute field must be a valid URL."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $startsWithHttp = strtolower(substr($value, 0, 7)) == "http://";
        $startsWithHttps = strtolower(substr($value, 0, 8)) == "https://";
        if (!$startsWithHttp && !$startsWithHttps) {
            $value = "http://" . $value;
        }
        return (bool) filter_var($value, FILTER_VALIDATE_URL);
    }
}