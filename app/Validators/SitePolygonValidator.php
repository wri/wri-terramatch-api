<?php

namespace App\Validators;

class SitePolygonValidator extends Validator
{
    public const FEATURE_BOUNDS = [
        'features' => 'required|array',
        'features.*' => 'polygon_feature_bounds',
    ];

    public const WITHIN_COUNTRY = [
        '*' => 'required|string|uuid|has_polygon_site|within_country',
    ];

    public const OVERLAPPING = [
        '*' => 'required|string|uuid|has_polygon_site|not_overlapping',
    ];

    public const SCHEMA = [
        'poly_name' => 'required',
        'plantstart' => 'required',
        'plantend' => 'required',
        'practice' => 'required',
        'target_sys' => 'required',
        'distr' => 'required',
        'num_trees' => 'required',
    ];

    public const DATA = [
        'poly_name' => 'required|string|not_in:null,NULL',
        'plantstart' => 'required|date|',
        'plantend' => 'required|date|',
        'practice' => 'required|string|not_in:null,NULL',
        'target_sys' => 'required|string|not_in:null,NULL',
        'distr' => 'required|string|not_in:null,NULL',
        'num_trees' => 'required|integer|',
    ];
}
