<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class LandType extends Extension
{
    public static $name = "land_type";
    public static $message = [
        "LAND_TYPE",
        "The {{attribute}} field must contain land types.",
        ["attribute" => ":attribute"],
        "The :attribute field must contain land types."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $landTypes = array_unique(array_values(Config::get("data.land_types")));
        return in_array($value, $landTypes);
    }
}
