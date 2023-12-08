<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateAdminRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'first_name' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
            'last_name' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
            'email_address' => [
                'sometimes',
                'required',
                'string',
                'email',
                'between:1,255',
            ],
            'password' => ['sometimes', 'string', Password::min(10)->mixedCase()->numbers()],
            'job_role' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
            'avatar' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'exists:uploads,id',
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
