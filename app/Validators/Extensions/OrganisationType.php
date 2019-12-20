<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class OrganisationType extends Extension
{
    public static $name = "organisation_type";
    public static $message = [
        "ORGANISATION_TYPE",
        "The {{attribute}} field must be an organisation type.",
        ["attribute" => ":attribute"],
        "The :attribute field must be an organisation type."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $organisationTypes = array_unique(array_values(Config::get("data.organisation_types")));
        return in_array($value, $organisationTypes);
    }
}
