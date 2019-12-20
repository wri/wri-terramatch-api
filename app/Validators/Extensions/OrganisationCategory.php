<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class OrganisationCategory extends Extension
{
    public static $name = "organisation_category";
    public static $message = [
        "ORGANISATION_CATEGORY",
        "The {{attribute}} field must be an organisation category.",
        ["attribute" => ":attribute"],
        "The :attribute field must be an organisation category."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $organisationCategories = array_unique(array_values(Config::get("data.organisation_categories")));
        return in_array($value, $organisationCategories);
    }
}