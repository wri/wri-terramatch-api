<?php

namespace App\Validators\Extensions;

class StartsWithFacebook extends Extension
{
    public static $name = "starts_with_facebook";
    public static $message = [
        "STARTS_WITH_FACEBOOK",
        "The {{attribute}} field must be a valid Facebook URL.",
        ["attribute" => ":attribute"],
        "The :attribute field must be a valid Facebook URL."
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
            strtolower(substr($value, 0, 16)) == "www.facebook.com" ||
            strtolower(substr($value, 0, 14)) == "facebook.com";
    }
}