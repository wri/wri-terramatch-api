<?php

namespace App\Validators;

class RestorationMethodMetricValidator extends Validator
{
    public const CREATE = [
        'pitch_id' => 'required|integer|exists:pitches,id',
        'restoration_method' => 'required|string|restoration_method',
        'experience' => 'required|integer|between:0,2147483647',
        'land_size' => 'required|numeric|strict_float|between:0,999999',
        'price_per_hectare' => 'required|numeric|strict_float|between:0,999999',
        'biomass_per_hectare' => 'present|nullable|numeric|strict_float|between:0,999999',
        'carbon_impact' => 'present|nullable|numeric|strict_float|between:0,999999',
        'species_impacted' => 'present|array|array_array',
        'species_impacted.*' => 'distinct|string|between:1,255',
    ];

    public const UPDATE = [
        'restoration_method' => 'sometimes|required|string|restoration_method',
        'experience' => 'sometimes|required|integer|between:0,2147483647',
        'land_size' => 'sometimes|required|numeric|strict_float|between:0,999999',
        'price_per_hectare' => 'sometimes|required|numeric|strict_float|between:0,999999',
        'biomass_per_hectare' => 'sometimes|present|nullable|numeric|strict_float|between:0,999999',
        'carbon_impact' => 'sometimes|present|nullable|numeric|strict_float|between:0,999999',
        'species_impacted' => 'sometimes|present|array|array_array',
        'species_impacted.*' => 'distinct|string|between:1,255',
    ];
}
