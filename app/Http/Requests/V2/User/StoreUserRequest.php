<?php

namespace App\Http\Requests\V2\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'type' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string',
            'role' => 'sometimes|nullable|string',

            'first_name' => 'required|string|between:1,255',
            'last_name' => 'required|string|between:1,255',
            'email_address' => 'required|string|email|between:1,255|unique:users,email_address',
            'password' => ['required', 'string', Password::min(8)->numbers()->mixedCase()],
            'job_role' => 'sometimes|nullable|string|between:1,255',
            'facebook' => 'sometimes|nullable|string|soft_url|starts_with_facebook|between:1,255',
            'twitter' => 'sometimes|nullable|string|soft_url|starts_with_twitter|between:1,255',
            'linkedin' => 'sometimes|nullable|string|soft_url|starts_with_linkedin|between:1,255',
            'instagram' => 'sometimes|nullable|string|soft_url|starts_with_instagram|between:1,255',
            'phone_number' => 'sometimes|nullable|string|between:1,255',
        ];
    }
}
