<?php

namespace App\Validators\Extensions;

class ContainUpper extends Extension
{
    public static $name = "contain_upper";
    public static $message = [
        "CONTAIN_UPPER",
        "The {{attribute}} field must contain an uppercase character.",
        ["attribute" => ":attribute"],
        "The :attribute field must contain an uppercase character."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $match = preg_match("/[A-Z]/", $value);
        return is_int($match) && $match == 1;
    }
}