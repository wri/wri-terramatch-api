<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendLoginDetailsRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email_address' => [
                'required',
                'string',
                'email',
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
