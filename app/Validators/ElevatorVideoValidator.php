<?php

namespace App\Validators;

class ElevatorVideoValidator extends Validator
{
    public const CREATE = [
        "introduction" => "required|integer|exists:uploads,id",
        "aims" => "required|integer|exists:uploads,id",
        "importance" => "required|integer|exists:uploads,id",
    ];
}