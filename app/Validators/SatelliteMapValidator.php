<?php

namespace App\Validators;

class SatelliteMapValidator extends Validator
{
    public const CREATE = [
        "monitoring_id" => "required|integer|exists:monitorings,id",
        "map" => "required|integer|exists:uploads,id",
        "alt_text" => "required|string|between:1,255"
    ];
}