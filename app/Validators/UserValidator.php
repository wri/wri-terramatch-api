<?php

namespace App\Validators;

class UserValidator extends Validator
{
    public const INVITE = [
        'email_address' => 'required|string|email|between:1,255|unique:users,email_address',
        'role' => 'in:user,terrafund_admin',
    ];

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
        'facebook' => 'sometimes|nullable|string|soft_url|starts_with_facebook|between:1,255',
        'twitter' => 'sometimes|nullable|string|soft_url|starts_with_twitter|between:1,255',
        'linkedin' => 'sometimes|nullable|string|soft_url|starts_with_linkedin|between:1,255',
        'instagram' => 'sometimes|nullable|string|soft_url|starts_with_instagram|between:1,255',
        'phone_number' => 'required|string|between:1,255',
        'whatsapp_phone' => 'sometimes|nullable|string|between:1,255',
    ];

    /**
     * This property deliberately omits the avatar validation rules. The upload
     * endpoint cannot be used until you're logged in. It wouldn't make sense to
     * require it here.
     */
    public const ACCEPT = [
        'first_name' => 'required|string|between:1,255',
        'last_name' => 'required|string|between:1,255',
        'email_address' => 'required|string|email|exists:users,email_address',
        'password' => 'required|string|min:8|contain_upper|contain_lower|contain_number',
        'job_role' => 'required|string|between:1,255',
        'facebook' => 'sometimes|nullable|string|soft_url|starts_with_facebook|between:1,255',
        'twitter' => 'sometimes|nullable|string|soft_url|starts_with_twitter|between:1,255',
        'linkedin' => 'sometimes|nullable|string|soft_url|starts_with_linkedin|between:1,255',
        'instagram' => 'sometimes|nullable|string|soft_url|starts_with_instagram|between:1,255',
        'phone_number' => 'required|string|between:1,255',
        'whatsapp_phone' => 'sometimes|string|between:1,255',
    ];

    public const UPDATE = [
        'first_name' => 'sometimes|required|string|between:1,255',
        'last_name' => 'sometimes|required|string|between:1,255',
        'email_address' => 'sometimes|required|string|email|between:1,255',
        'password' => 'sometimes|required|string|min:10|contain_upper|contain_lower|contain_number',
        'job_role' => 'sometimes|required|string|between:1,255',
        'facebook' => 'sometimes|present|nullable|string|soft_url|starts_with_facebook|between:1,255',
        'twitter' => 'sometimes|present|nullable|string|soft_url|starts_with_twitter|between:1,255',
        'linkedin' => 'sometimes|present|nullable|string|soft_url|starts_with_linkedin|between:1,255',
        'instagram' => 'sometimes|present|nullable|string|soft_url|starts_with_instagram|between:1,255',
        'avatar' => 'sometimes|present|nullable|integer|exists:uploads,id',
        'phone_number' => 'sometimes|required|string|between:1,255',
        'whatsapp_phone' => 'sometimes|string|between:1,255',
    ];

    public const UPDATE_ROLE = [
        'role' => 'required|string|in:user,terrafund_admin',
    ];

    public const RESEND = [
        'uuid' => 'required|string',
    ];
}
