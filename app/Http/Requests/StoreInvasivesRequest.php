<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvasivesRequest extends FormRequest
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
                'between:1,255',
            ],
            'type' => [
                'required',
                'string',
                'in:dominant_species,common,uncommon',
            ],
        ];
    }
}
