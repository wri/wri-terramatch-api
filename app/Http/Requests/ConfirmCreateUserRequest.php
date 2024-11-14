<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ConfirmCreateUserRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'token' => [
                'required',
                'string',
                'exists:password_resets,token',
            ],
            'password' => ['required', 'string', Password::min(10)->mixedCase()->numbers()],
            'first_name' => 'required|string|between:1,255',
            'last_name' => 'required|string|between:1,255',
            'email_address' => 'required|string|email|between:1,255|unique:users,email_address',
            'role' => 'sometimes|nullable|string',
            'job_role' => 'sometimes|nullable|string|between:1,255',
            'phone_number' => 'sometimes|nullable|string|between:1,255',
        ];
    }
}
