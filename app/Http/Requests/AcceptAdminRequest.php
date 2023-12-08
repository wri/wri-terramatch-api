<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class AcceptAdminRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'first_name' => [
                'required',
                'string',
                'between:1,255',
            ],
            'last_name' => [
                'required',
                'string',
                'between:1,255',
            ],
            'email_address' => [
                'required',
                'string',
                'email',
                'exists:users,email_address',
            ],
            'password' => ['required', 'string', Password::min(10)->mixedCase()->numbers()],
            'job_role' => [
                'required',
                'string',
                'between:1,255',
            ],
            'callback_url' => [
                'sometimes',
                'string',
                'url',
                'max:5000',
            ],
        ];
    }
}
