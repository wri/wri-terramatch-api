<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResendRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'callback_url' => [
                'sometimes',
                'string',
                'url',
                'max:5000',
            ],
        ];
    }
}
