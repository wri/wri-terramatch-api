<?php

namespace App\Validators;

class OfferValidator extends Validator
{
    public $create = [
        "name" => "required|string|between:1,255",
        "description" => "required|string|min:8",
        "land_types" => "required|array",
        "land_types.*" => "distinct|string|land_type",
        "land_ownerships" => "required|array",
        "land_ownerships.*" => "distinct|string|land_ownership",
        "land_size" => "required|string|land_size",
        "land_continent" => "required|string|continent",
        "land_country" => "present|nullable|string|country_code",
        "restoration_methods" => "required|array",
        "restoration_methods.*" => "distinct|string|restoration_method",
        "restoration_goals" => "required|array",
        "restoration_goals.*" => "distinct|string|restoration_goal",
        "funding_sources" => "required|array",
        "funding_sources.*" => "distinct|string|funding_source",
        "funding_amount" => "required|numeric|min:0",
        "price_per_tree" => "present|nullable|numeric|min:0",
        "long_term_engagement" => "present|nullable|boolean",
        "reporting_frequency" => "required|string|reporting_frequency",
        "reporting_level" => "required|string|reporting_level",
        "sustainable_development_goals" => "required|array",
        "sustainable_development_goals.*" => "distinct|string|sustainable_development_goal",
        "avatar" => "present|nullable|integer|exists:uploads,id",
        "cover_photo" => "present|nullable|integer|exists:uploads,id",
        "video" => "present|nullable|integer|exists:uploads,id"
    ];

    public $update = [
        "name" => "sometimes|required|string|between:1,255",
        "description" => "sometimes|required|string|min:8",
        "land_types" => "sometimes|required|array",
        "land_types.*" => "distinct|string|land_type",
        "land_ownerships" => "sometimes|required|array",
        "land_ownerships.*" => "distinct|string|land_ownership",
        "land_size" => "sometimes|required|string|land_size",
        "land_continent" => "sometimes|required|string|continent",
        "land_country" => "sometimes|present|nullable|string|country_code",
        "restoration_methods" => "sometimes|required|array",
        "restoration_methods.*" => "distinct|string|restoration_method",
        "restoration_goals" => "sometimes|required|array",
        "restoration_goals.*" => "distinct|string|restoration_goal",
        "funding_sources" => "sometimes|required|array",
        "funding_sources.*" => "distinct|string|funding_source",
        "funding_amount" => "sometimes|required|numeric|min:0",
        "price_per_tree" => "sometimes|present|nullable|numeric|min:0",
        "long_term_engagement" => "sometimes|present|nullable|boolean",
        "reporting_frequency" => "sometimes|required|string|reporting_frequency",
        "reporting_level" => "sometimes|required|string|reporting_level",
        "sustainable_development_goals" => "sometimes|required|array",
        "sustainable_development_goals.*" => "distinct|string|sustainable_development_goal",
        "avatar" => "sometimes|present|nullable|integer|exists:uploads,id",
        "cover_photo" => "sometimes|present|nullable|integer|exists:uploads,id",
        "video" => "sometimes|present|nullable|integer|exists:uploads,id"
    ];

    public $complete = [
        "successful" => "required|boolean"
    ];
}