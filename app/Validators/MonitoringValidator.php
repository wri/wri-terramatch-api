<?php

namespace App\Validators;

class MonitoringValidator extends Validator
{
    public const CREATE = [
        "match_id" => "required|integer|exists:matches,id|unique:monitorings,match_id"
    ];
}