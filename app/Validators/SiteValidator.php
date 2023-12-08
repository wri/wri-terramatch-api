<?php

namespace App\Validators;

class SiteValidator extends Validator
{
    public const ATTACH_LAND_TENURE = [
        'land_tenure_ids' => 'required|array',
        'land_tenure_ids.*' => 'integer|exists:land_tenures,id',
    ];

    public const ATTACH_RESTORATION_METHODS = [
        'site_restoration_method_ids' => 'required|array',
        'site_restoration_method_ids.*' => 'integer|exists:site_restoration_methods,id',
    ];

    public const UPDATE_ESTABLISHMENT_DATE = [
        'establishment_date' => 'required|date_format:Y-m-d',
    ];
}
