<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class Continent extends Extension
{
    public static $name = "continent";
    public static $message = [
        "CONTINENT",
        "The {{attribute}} field must be a continent.",
        ["attribute" => ":attribute"],
        "The :attribute field must be a continent."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $continents = array_unique(array_values(Config::get("data.continents")));
        return in_array($value, $continents);
    }
}
