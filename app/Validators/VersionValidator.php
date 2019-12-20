<?php

namespace App\Validators;

class VersionValidator extends Validator
{
    public $reject = [
        "rejected_reason" => "required|string|min:8"
    ];
}