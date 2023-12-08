<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResendByEmailRequest extends FormRequest
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
                'max:5000',
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
