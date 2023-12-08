<?php

namespace App\Validators\Extensions;

class OrganisationCategory extends Extension
{
    public static $name = 'organisation_category';

    public static $message = [
        'ORGANISATION_CATEGORY',
        'The {{attribute}} field must be an organisation category.',
        ['attribute' => ':attribute'],
        'The :attribute field must be an organisation category.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $organisationCategories = array_unique(array_values(config('data.organisation_categories')));

        return in_array($value, $organisationCategories);
    }
}
