<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeviceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'uuid' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
            'push_token' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
                'unique:devices,push_token',
            ],
        ];
    }
}
