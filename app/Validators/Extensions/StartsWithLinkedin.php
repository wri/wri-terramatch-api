<?php

namespace App\Validators\Extensions;

class StartsWithLinkedin extends Extension
{
    public static $name = "starts_with_linkedin";
    public static $message = [
        "STARTS_WITH_LINKEDIN",
        "The {{attribute}} field must be a valid LinkedIn URL.",
        ["attribute" => ":attribute"],
        "The :attribute field must be a valid LinkedIn URL."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $startsWithHttp = strtolower(substr($value, 0, 7)) == "http://";
        $startsWithHttps = strtolower(substr($value, 0, 8)) == "https://";
        if ($startsWithHttp) {
            $value = substr($value, 7);
        } else if ($startsWithHttps) {
            $value = substr($value, 8);
        }
        return
            strtolower(substr($value, 0, 16)) == "www.linkedin.com" ||
            strtolower(substr($value, 0, 12)) == "linkedin.com";
    }
}