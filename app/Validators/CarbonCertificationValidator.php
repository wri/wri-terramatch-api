<?php

namespace App\Validators;

class CarbonCertificationValidator extends Validator
{
    public const CREATE = [
        "pitch_id" => "required|integer|exists:pitches,id",
        "type" => "required|string|carbon_certification_type",
        "other_value" => "other_value_present|other_value_null|other_value_string",
        "link" => "required|string|soft_url|between:1,255",
    ];

    public const UPDATE = [
        "type" => "sometimes|required|string|carbon_certification_type",
        "other_value" => "other_value_present|other_value_null|other_value_string",
        "link" => "sometimes|required|string|soft_url|between:1,255"
    ];
}
