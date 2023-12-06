<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTreeSpeciesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
            'is_native' => [
                'sometimes',
                'required',
                'boolean',
            ],
            'count' => [
                'sometimes',
                'required',
                'integer',
                'between:0,2147483647',
            ],
            'price_to_plant' => [
                'sometimes',
                'required',
                'numeric',
                'between:0,999999',
            ],
            'price_to_maintain' => [
                'sometimes',
                'required',
                'numeric',
                'between:0,999999',
            ],
            'saplings' => [
                'sometimes',
                'required',
                'numeric',
                'between:0,999999',
            ],
            'site_prep' => [
                'sometimes',
                'required',
                'numeric',
                'between:0,999999',
            ],
            'survival_rate' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'between:0,100',
            ],
            'produces_food' => [
                'sometimes',
                'present',
                'nullable',
                'boolean',
            ],
            'produces_firewood' => [
                'sometimes',
                'present',
                'nullable',
                'boolean',
            ],
            'produces_timber' => [
                'sometimes',
                'present',
                'nullable',
                'boolean',
            ],
            'owner' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'between:1,255',
            ],
            'season' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
        ];
    }
}
