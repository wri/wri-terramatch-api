<?php

namespace App\Validators;

class TreeSpeciesValidator extends Validator
{
    public $create = [
        "pitch_id" => "required|integer|exists:pitches,id",
        "name" => "required|string|between:1,255",
        "is_native" => "required|boolean",
        "count" => "required|integer|min:1",
        "price_to_plant" => "required|numeric|min:0",
        "price_to_maintain" => "required|numeric|min:0",
        "saplings" => "required|numeric|min:0",
        "site_prep" => "required|numeric|min:0",
        "survival_rate" => "required|integer|between:0,100",
        "produces_food" => "present|nullable|boolean",
        "produces_firewood" => "present|nullable|boolean",
        "produces_timber" => "present|nullable|boolean",
        "owner" => "required|string|tree_species_owner",
        "season" => "required|string|between:1,255"
    ];

    public $update = [
        "name" => "sometimes|required|string|between:1,255",
        "is_native" => "sometimes|required|boolean",
        "count" => "sometimes|required|integer|min:1",
        "price_to_plant" => "sometimes|required|numeric|min:0",
        "price_to_maintain" => "sometimes|required|numeric|min:0",
        "saplings" => "sometimes|required|numeric|min:0",
        "site_prep" => "sometimes|required|numeric|min:0",
        "survival_rate" => "sometimes|required|integer|between:0,100",
        "produces_food" => "sometimes|present|nullable|boolean",
        "produces_firewood" => "sometimes|present|nullable|boolean",
        "produces_timber" => "sometimes|present|nullable|boolean",
        "owner" => "sometimes|required|string|tree_species_owner",
        "season" => "sometimes|required|string|between:1,255",
    ];
}
