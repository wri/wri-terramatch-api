<?php

namespace App\Validators;

class DeviceValidator extends Validator
{
    public $create = [
        "os" => "required|string|in:ios,android",
        "uuid" => "required|string|min:1",
        "push_token" => "required|string|min:1"
    ];

    public $update = [
        "uuid" => "sometimes|required|string|min:1",
        "push_token" => "sometimes|required|string|min:1"
    ];
}