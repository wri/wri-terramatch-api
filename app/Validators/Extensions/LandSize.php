<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class LandSize extends Extension
{
    public static $name = "land_size";
    public static $message = [
        "LAND_SIZE",
        "The {{attribute}} field must be a land size.",
        ["attribute" => ":attribute"],
        "The :attribute field must be a land size."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $landSizes = array_unique(array_values(Config::get("data.land_sizes")));
        return in_array($value, $landSizes);
    }
}
