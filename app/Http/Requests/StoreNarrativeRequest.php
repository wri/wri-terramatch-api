<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNarrativeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'technical_narrative' => [
                'required',
                'string',
                'between:1,65535',
            ],
            'public_narrative' => [
                'required',
                'string',
                'between:1,65535',
            ],
        ];
    }
}
