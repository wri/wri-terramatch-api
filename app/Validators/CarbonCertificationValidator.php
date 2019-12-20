<?php

namespace App\Validators;

class CarbonCertificationValidator extends Validator
{
    public $create = [
        "pitch_id" => "required|integer|exists:pitches,id",
        "type" => "required|string|carbon_certification_type",
        "other_type" => "required_if:type,other|string",
        "link" => "required|string|soft_url|between:1,255",
    ];

    public $update = [
        "type" => "sometimes|required|string|carbon_certification_type",
        "other_type" => "required_if:type,other|string",
        "link" => "sometimes|required|string|soft_url|between:1,255"
    ];
}
