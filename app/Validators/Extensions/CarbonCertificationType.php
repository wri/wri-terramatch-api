<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class CarbonCertificationType extends Extension
{
    public static $name = "carbon_certification_type";
    public static $message = [
        "CARBON_CERTIFICATION_TYPE",
        "The {{attribute}} field must be a carbon certification type.",
        ["attribute" => ":attribute"],
        "The :attribute field must be a carbon certification type."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $carbonCertificationTypes = array_unique(array_values(Config::get("data.carbon_certification_types")));
        return in_array($value, $carbonCertificationTypes);
    }
}
