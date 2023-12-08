<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InviteAdminRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'email_address' => [
                'required',
                'string',
                'email',
                'between:1,255',
                'unique:users,email_address',
            ],
        ];
    }
}
