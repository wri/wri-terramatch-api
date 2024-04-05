<?php

namespace App\Validators;

class ServiceAccountValidator extends Validator
{
    public const CREATE = [
        'email_address' => 'required|string|email|between:1,255|unique:users,email_address',
        'api_key' => 'required|string|size:64|unique:users,api_key',
    ];
}
