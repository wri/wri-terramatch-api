<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class ReportingLevel extends Extension
{
    public static $name = "reporting_level";
    public static $message = [
        "REPORTING_LEVEL",
        "The {{attribute}} field must be a reporting level.",
        ["attribute" => ":attribute"],
        "The :attribute field must be a reporting level."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $reportingLevels = array_unique(array_values(Config::get("data.reporting_levels")));
        return in_array($value, $reportingLevels);
    }
}
