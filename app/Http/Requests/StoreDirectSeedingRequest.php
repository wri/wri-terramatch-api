<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDirectSeedingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
            ],
            'weight' => [
                'required',
                'integer',
            ],
        ];
    }
}
