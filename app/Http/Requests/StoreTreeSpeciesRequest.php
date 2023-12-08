<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTreeSpeciesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'pitch_id' => [
                'required',
                'integer',
                'exists:pitches,id',
            ],
            'name' => [
                'required',
                'string',
                'between:1,255',
            ],
            'is_native' => [
                'required',
                'boolean',
            ],
            'count' => [
                'required',
                'integer',
                'between:0,2147483647',
            ],
            'price_to_plant' => [
                'required',
                'numeric',
                'between:0,999999',
            ],
            'price_to_maintain' => [
                'required',
                'numeric',
                'between:0,999999',
            ],
            'saplings' => [
                'required',
                'numeric',
                'between:0,999999',
            ],
            'site_prep' => [
                'required',
                'numeric',
                'between:0,999999',
            ],
            'survival_rate' => [
                'present',
                'nullable',
                'integer',
                'between:0,100',
            ],
            'produces_food' => [
                'present',
                'nullable',
                'boolean',
            ],
            'produces_firewood' => [
                'present',
                'nullable',
                'boolean',
            ],
            'produces_timber' => [
                'present',
                'nullable',
                'boolean',
            ],
            'owner' => [
                'present',
                'nullable',
                'string',
                'between:1,255',
            ],
            'season' => [
                'required',
                'string',
                'between:1,255',
            ],
        ];
    }
}
