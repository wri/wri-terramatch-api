<?php

namespace App\Validators\Extensions;

class ContainLower extends Extension
{
    public static $name = "contain_lower";
    public static $message = [
        "CONTAIN_LOWER",
        "The {{attribute}} field must contain a lowercase character.",
        ["attribute" => ":attribute"],
        "The :attribute field must contain a lowercase character."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $match = preg_match("/[a-z]/", $value);
        return is_int($match) && $match == 1;
    }
}