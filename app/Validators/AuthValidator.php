<?php

namespace App\Validators;

class AuthValidator extends Validator
{
    public const LOGIN = [
        "email_address" => "required|string|email",
        "password" => "required|string"
    ];

    public const VERIFY = [
        "token" => "required|string|exists:verifications,token",
    ];

    public const RESET = [
        "email_address" => "required|string|email"
    ];

    public const CHANGE = [
        "token" => "required|string|exists:password_resets,token",
        "password" => "required|string|min:10|contain_upper|contain_lower|contain_number"
    ];
}