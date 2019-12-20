<?php

namespace App\Validators;

class RestorationMethodMetricValidator extends Validator
{
    public $create = [
        "pitch_id" => "required|integer|exists:pitches,id",
        "restoration_method" => "required|string|restoration_method",
        "experience" => "required|integer|min:1",
        "land_size" => "required|numeric|min:0",
        "price_per_hectare" => "required|numeric|min:0",
        "biomass_per_hectare" => "present|nullable|numeric|min:0",
        "carbon_impact" => "present|nullable|numeric|min:0",
        "species_impacted" => "present|array",
        "species_impacted.*" => "distinct|string|between:1,255"
    ];

    public $update = [
        "restoration_method" => "sometimes|required|string|restoration_method",
        "experience" => "sometimes|required|integer|min:1",
        "land_size" => "sometimes|required|numeric|min:0",
        "price_per_hectare" => "sometimes|required|numeric|min:0",
        "biomass_per_hectare" => "sometimes|present|nullable|numeric|min:0",
        "carbon_impact" => "sometimes|present|nullable|numeric|min:0",
        "species_impacted" => "sometimes|present|array",
        "species_impacted.*" => "distinct|string|between:1,255"
    ];
}