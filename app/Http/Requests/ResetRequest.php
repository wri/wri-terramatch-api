<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetRequest extends FormRequest
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
