<?php

namespace App\Validators;

class AuthValidator extends Validator
{
    public $login = [
        "email_address" => "required|string|email",
        "password" => "required|string"
    ];

    public $verify = [
        "token" => "required|string|between:1,255|exists:verifications,token",
    ];

    public $reset = [
        "email_address" => "required|string|email|between:1,255"
    ];

    public $change = [
        "token" => "required|string|between:1,255|exists:password_resets,token",
        "password" => "required|string|between:8,255"
    ];
}