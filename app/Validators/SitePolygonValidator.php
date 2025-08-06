<?php

namespace App\Validators;

class SitePolygonValidator extends Validator
{
    public const FEATURE_BOUNDS = [
        'features' => 'required|array',
        'features.*' => 'polygon_feature_bounds',
    ];

    public const FEATURE_BOUNDS_UUID = [
        '*' => 'string|uuid||polygon_feature_bounds',
    ];

    public const SPIKES = [
        'features' => 'required|array',
        'features.*.geometry' => 'polygon_spikes',
    ];

    public const SPIKES_UUID = [
        '*' => 'string|uuid|has_polygon_site|polygon_spikes',
    ];

    public const POLYGON_SIZE = [
        'features' => 'required|array',
        'features.*' => 'polygon_size',
    ];

    public const POLYGON_SIZE_UUID = [
        '*' => 'string|uuid|has_polygon_site|polygon_size',
    ];

    public const SELF_INTERSECTION = [
        'features' => 'required|array',
        'features.*' => 'polygon_self_intersection',
    ];

    public const SELF_INTERSECTION_UUID = [
        '*' => 'string|uuid|has_polygon_site|polygon_self_intersection',
    ];

    public const WITHIN_COUNTRY = [
        '*' => 'string|uuid|has_polygon_site|within_country',
    ];

    public const NOT_OVERLAPPING = [
        '*' => 'string|uuid|has_polygon_site|not_overlapping',
    ];

    public const ESTIMATED_AREA = [
        '*' => 'string|uuid|has_polygon_site|estimated_area',
    ];

    public const PLANT_START_DATE = [
        '*' => 'string|uuid|has_polygon_site|plant_start_date',
    ];

    public const GEOMETRY_TYPE = [
      'type' => 'required|in:FeatureCollection',
      'features' => 'required|array|geometry_type',
    ];

    public const GEOMETRY_TYPE_UUID = [
        '*' => 'string|uuid|geometry_type',
    ];

    public const SCHEMA = [
        'features' => 'required|array',
        'features.*.properties.poly_name' => 'required',
        'features.*.properties.plantstart' => 'required',
        'features.*.properties.practice' => 'required',
        'features.*.properties.target_sys' => 'required',
        'features.*.properties.distr' => 'required',
        'features.*.properties.num_trees' => 'required',
    ];

    public const DATA = [
        'features' => 'required|array',
        'features.*.properties.poly_name' => 'string|not_in:null,NULL|filled',
        'features.*.properties.plantstart' => 'date',
        'features.*.properties.practice' => 'string|not_in:null,NULL|filled',
        'features.*.properties.target_sys' => 'string|not_in:null,NULL|filled',
        'features.*.properties.distr' => 'string|not_in:null,NULL|filled',
        'features.*.properties.num_trees' => 'integer',
    ];
}
