<?php

namespace App\Validators;

class SitePolygonValidator extends Validator
{
    public const FEATURE_BOUNDS = [
        'features' => 'required|array',
        'features.*' => 'polygon_feature_bounds',
    ];

    public const SPIKES = [
        'features' => 'required|array',
        'features.*.geometry' => 'polygon_spikes',
    ];

    public const POLYGON_SIZE = [
        'features' => 'required|array',
        'features.*' => 'polygon_size',
    ];

    public const SELF_INTERSECTION = [
        'features' => 'required|array',
        'features.*' => 'polygon_self_intersection',
    ];

    public const WITHIN_COUNTRY = [
        '*' => 'string|uuid|has_polygon_site|within_country',
    ];

    public const OVERLAPPING = [
        '*' => 'string|uuid|has_polygon_site|not_overlapping',
    ];

    public const SCHEMA = [
        'features' => 'required|array',
        'features.*.properties.poly_name' => 'required',
        'features.*.properties.plantstart' => 'required',
        'features.*.properties.plantend' => 'required',
        'features.*.properties.practice' => 'required',
        'features.*.properties.target_sys' => 'required',
        'features.*.properties.distr' => 'required',
        'features.*.properties.num_trees' => 'required',
    ];

    public const DATA = [
        'features' => 'required|array',
        'features.*.properties.poly_name' => 'required|string|not_in:null,NULL',
        'features.*.properties.plantstart' => 'required|date|',
        'features.*.properties.plantend' => 'required|date|',
        'features.*.properties.practice' => 'required|string|not_in:null,NULL',
        'features.*.properties.target_sys' => 'required|string|not_in:null,NULL',
        'features.*.properties.distr' => 'required|string|not_in:null,NULL',
        'features.*.properties.num_trees' => 'required|integer|',
    ];
}
