<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class Visibility extends Extension
{
    public static $name = "visibility";
    public static $message = [
        "VISIBILITY",
        "The {{attribute}} field must be a visibility.",
        ["attribute" => ":attribute"],
        "The :attribute field must be a visibility."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $visibilities = array_unique(array_values(Config::get("data.visibilities")));
        return in_array($value, $visibilities);
    }
}
