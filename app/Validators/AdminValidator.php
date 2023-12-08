<?php

namespace App\Validators;

class AdminValidator extends Validator
{
    /**
     * This property deliberately omits the avatar validation rules. The upload
     * endpoint cannot be used until you're logged in. It wouldn't make sense to
     * require it here.
     */
    public const CREATE = [
        'first_name' => 'required|string|between:1,255',
        'last_name' => 'required|string|between:1,255',
        'email_address' => 'required|string|email|between:1,255|unique:users,email_address',
        'password' => 'required|string|min:8|contain_upper|contain_lower|contain_number',
        'job_role' => 'required|string|between:1,255',
    ];
}
