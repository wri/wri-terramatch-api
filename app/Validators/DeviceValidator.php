<?php

namespace App\Validators;

class DeviceValidator extends Validator
{
    public const CREATE = [
        "os" => "required|string|in:ios,android",
        "uuid" => "required|string|between:1,255",
        "push_token" => "required|string|between:1,255|unique:devices,push_token"
    ];

    public const UPDATE = [
        "uuid" => "sometimes|required|string|between:1,255",
        "push_token" => "sometimes|required|string|between:1,255|unique:devices,push_token"
    ];
}