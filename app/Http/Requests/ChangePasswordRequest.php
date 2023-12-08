<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
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
        ];
    }
}
