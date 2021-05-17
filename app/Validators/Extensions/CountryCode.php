<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class CountryCode extends Extension
{
    public static $name = "country_code";
    public static $message = [
        "COUNTRY_CODE",
        "The {{attribute}} field must be an ISO country code.",
        ["attribute" => ":attribute"],
        "The :attribute field must be an ISO country code."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $countries = array_unique(array_values(Config::get("data.countries")));
        return in_array($value, $countries);
    }
}