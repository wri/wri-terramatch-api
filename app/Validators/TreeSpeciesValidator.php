<?php

namespace App\Validators;

class TreeSpeciesValidator extends Validator
{
    public const CREATE = [
        "pitch_id" => "required|integer|exists:pitches,id",
        "name" => "required|string|between:1,255",
        "is_native" => "required|boolean",
        "count" => "required|integer|between:1,2147483647",
        "price_to_plant" => "required|numeric|strict_float|between:0,999999",
        "price_to_maintain" => "required|numeric|strict_float|between:0,999999",
        "saplings" => "required|numeric|strict_float|between:0,999999",
        "site_prep" => "required|numeric|strict_float|between:0,999999",
        "survival_rate" => "present|nullable|integer|between:0,100",
        "produces_food" => "present|nullable|boolean",
        "produces_firewood" => "present|nullable|boolean",
        "produces_timber" => "present|nullable|boolean",
        "owner" => "present|nullable|string|between:1,255",
        "season" => "required|string|between:1,255"
    ];

    public const UPDATE = [
        "name" => "sometimes|required|string|between:1,255",
        "is_native" => "sometimes|required|boolean",
        "count" => "sometimes|required|integer|between:1,2147483647",
        "price_to_plant" => "sometimes|required|numeric|strict_float|between:0,999999",
        "price_to_maintain" => "sometimes|required|numeric|strict_float|between:0,999999",
        "saplings" => "sometimes|required|numeric|strict_float|between:0,999999",
        "site_prep" => "sometimes|required|numeric|strict_float|between:0,999999",
        "survival_rate" => "sometimes|present|nullable|integer|between:0,100",
        "produces_food" => "sometimes|present|nullable|boolean",
        "produces_firewood" => "sometimes|present|nullable|boolean",
        "produces_timber" => "sometimes|present|nullable|boolean",
        "owner" => "sometimes|present|nullable|string|between:1,255",
        "season" => "sometimes|required|string|between:1,255",
    ];
}
