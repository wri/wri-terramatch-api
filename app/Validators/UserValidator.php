<?php

namespace App\Validators;

class UserValidator extends Validator
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
        'country' => 'sometimes|nullable|between:1,255',
        'program' => 'sometimes|nullable|between:1,255',
        'role' => 'required|string|between:1,255',
        'facebook' => 'sometimes|nullable|string|soft_url|starts_with_facebook|between:1,255',
        'twitter' => 'sometimes|nullable|string|soft_url|starts_with_twitter|between:1,255',
        'linkedin' => 'sometimes|nullable|string|soft_url|starts_with_linkedin|between:1,255',
        'instagram' => 'sometimes|nullable|string|soft_url|starts_with_instagram|between:1,255',
        'phone_number' => 'required|string|between:1,255',
        'whatsapp_phone' => 'sometimes|nullable|string|between:1,255',
    ];
}
