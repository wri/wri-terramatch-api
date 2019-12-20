<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class RestorationMethod extends Extension
{
    public static $name = "restoration_method";
    public static $message = [
        "RESTORATION_METHOD",
        "The {{attribute}} field must contain restoration methods.",
        ["attribute" => ":attribute"],
        "The :attribute field must contain restoration methods."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $restorationMethods = array_unique(array_values(Config::get("data.restoration_methods")));
        return in_array($value, $restorationMethods);
    }
}
