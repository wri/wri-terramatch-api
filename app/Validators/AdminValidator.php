<?php

namespace App\Validators;

class AdminValidator extends Validator
{
    public const INVITE = [
        "email_address" => "required|string|email|between:1,255|unique:users,email_address"
    ];

    /**
     * This property deliberately omits the avatar validation rules. The upload
     * endpoint cannot be used until you're logged in. It wouldn't make sense to
     * require it here.
     */
    public const CREATE = [
        "first_name" => "required|string|between:1,255",
        "last_name" => "required|string|between:1,255",
        "email_address" => "required|string|email|between:1,255|unique:users,email_address",
        "password" => "required|string|min:10|contain_upper|contain_lower|contain_number",
        "job_role" => "required|string|between:1,255",
    ];

    /**
     * This property deliberately omits the avatar validation rules. The upload
     * endpoint cannot be used until you're logged in. It wouldn't make sense to
     * require it here.
     */
    public const ACCEPT = [
        "first_name" => "required|string|between:1,255",
        "last_name" => "required|string|between:1,255",
        "email_address" => "required|string|email|exists:users,email_address",
        "password" => "required|string|min:10|contain_upper|contain_lower|contain_number",
        "job_role" => "required|string|between:1,255",
    ];

    public const UPDATE = [
        "first_name" => "sometimes|required|string|between:1,255",
        "last_name" => "sometimes|required|string|between:1,255",
        "email_address" => "sometimes|required|string|email|between:1,255",
        "password" => "sometimes|required|string|min:10|contain_upper|contain_lower|contain_number",
        "job_role" => "sometimes|required|string|between:1,255",
        "avatar" => "sometimes|present|nullable|integer|exists:uploads,id"
    ];
}