<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'os' => [
                'required',
                'string',
                'in:ios,android',
            ],
            'uuid' => [
                'required',
                'string',
                'between:1,255',
            ],
            'push_token' => [
                'required',
                'string',
                'between:1,255',
                'unique:devices,push_token',
            ],
        ];
    }
}
