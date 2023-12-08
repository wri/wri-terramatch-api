<?php

namespace App\Validators;

class TeamMemberValidator extends Validator
{
    public const CREATE = [
        'first_name' => 'required|string|between:1,255',
        'last_name' => 'required|string|between:1,255',
        'job_role' => 'required|string|between:1,255',
        'facebook' => 'present|nullable|string|soft_url|starts_with_facebook|between:1,255',
        'twitter' => 'present|nullable|string|soft_url|starts_with_twitter|between:1,255',
        'linkedin' => 'present|nullable|string|soft_url|starts_with_linkedin|between:1,255',
        'instagram' => 'present|nullable|string|soft_url|starts_with_instagram|between:1,255',
        'avatar' => 'present|nullable|integer|exists:uploads,id',
        'phone_number' => 'present|nullable|string|between:1,255',
        'email_address' => 'present|nullable|string|email|between:1,255',
    ];

    public const UPDATE = [
        'first_name' => 'sometimes|required|string|between:1,255',
        'last_name' => 'sometimes|required|string|between:1,255',
        'job_role' => 'sometimes|required|string|between:1,255',
        'facebook' => 'sometimes|present|nullable|string|soft_url|starts_with_facebook|between:1,255',
        'twitter' => 'sometimes|present|nullable|string|soft_url|starts_with_twitter|between:1,255',
        'linkedin' => 'sometimes|present|nullable|string|soft_url|starts_with_linkedin|between:1,255',
        'instagram' => 'sometimes|present|nullable|string|soft_url|starts_with_instagram|between:1,255',
        'avatar' => 'sometimes|present|nullable|integer|exists:uploads,id',
        'phone_number' => 'sometimes|present|nullable|string|between:1,255',
        'email_address' => 'sometimes|present|nullable|string|email|between:1,255',
    ];
}
