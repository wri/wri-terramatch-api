<?php

namespace App\Validators;

class OfferValidator extends Validator
{
    public const CREATE = [
        'name' => 'required|string|between:1,255',
        'description' => 'required|string|between:1,65535',
        'land_types' => 'required|array|array_array',
        'land_types.*' => 'distinct|string|land_type',
        'land_ownerships' => 'required|array|array_array',
        'land_ownerships.*' => 'distinct|string|land_ownership',
        'land_size' => 'required|string|land_size',
        'land_continent' => 'required|string|continent',
        'land_country' => 'present|nullable|string|country_code',
        'restoration_methods' => 'required|array|array_array',
        'restoration_methods.*' => 'distinct|string|restoration_method',
        'restoration_goals' => 'required|array|array_array',
        'restoration_goals.*' => 'distinct|string|restoration_goal',
        'funding_sources' => 'required|array|array_array',
        'funding_sources.*' => 'distinct|string|funding_source',
        'funding_amount' => 'present|nullable|integer|between:1,2147483647',
        'funding_bracket' => 'required|string|funding_bracket',
        'price_per_tree' => 'present|nullable|numeric|strict_float|between:0,999999',
        'long_term_engagement' => 'present|nullable|boolean',
        'reporting_frequency' => 'required|string|reporting_frequency',
        'reporting_level' => 'required|string|reporting_level',
        'sustainable_development_goals' => 'present|array|array_array',
        'sustainable_development_goals.*' => 'distinct|string|sustainable_development_goal',
        'cover_photo' => 'present|nullable|integer|exists:uploads,id',
        'video' => 'present|nullable|integer|exists:uploads,id',
    ];

    public const UPDATE = [
        'name' => 'sometimes|required|string|between:1,255',
        'description' => 'sometimes|required|string|between:1,65535',
        'land_types' => 'sometimes|required|array|array_array',
        'land_types.*' => 'distinct|string|land_type',
        'land_ownerships' => 'sometimes|required|array|array_array',
        'land_ownerships.*' => 'distinct|string|land_ownership',
        'land_size' => 'sometimes|required|string|land_size',
        'land_continent' => 'sometimes|required|string|continent',
        'land_country' => 'sometimes|present|nullable|string|country_code',
        'restoration_methods' => 'sometimes|required|array|array_array',
        'restoration_methods.*' => 'distinct|string|restoration_method',
        'restoration_goals' => 'sometimes|required|array|array_array',
        'restoration_goals.*' => 'distinct|string|restoration_goal',
        'funding_sources' => 'sometimes|required|array|array_array',
        'funding_sources.*' => 'distinct|string|funding_source',
        'funding_amount' => 'sometimes|present|nullable|integer|between:1,2147483647',
        'funding_bracket' => 'sometimes|required|string|funding_bracket',
        'price_per_tree' => 'sometimes|present|nullable|numeric|strict_float|between:0,999999',
        'long_term_engagement' => 'sometimes|present|nullable|boolean',
        'reporting_frequency' => 'sometimes|required|string|reporting_frequency',
        'reporting_level' => 'sometimes|required|string|reporting_level',
        'sustainable_development_goals' => 'sometimes|present|array|array_array',
        'sustainable_development_goals.*' => 'distinct|string|sustainable_development_goal',
        'cover_photo' => 'sometimes|present|nullable|integer|exists:uploads,id',
        'video' => 'sometimes|present|nullable|integer|exists:uploads,id',
    ];

    public const UPDATE_VISIBILITY = [
        'visibility' => 'required|string|visibility',
    ];
}
