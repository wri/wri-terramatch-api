<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreControlAimsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'aim_survival_rate' => [
                'nullable',
                'integer',
                'between:0,100',
            ],
            'aim_year_five_crown_cover' => [
                'nullable',
                'integer',
                'between:0,100',
            ],
            'aim_direct_seeding_survival_rate' => [
                'nullable',
                'integer',
                'between:0,100',
            ],
            'aim_natural_regeneration_trees_per_hectare' => [
                'nullable',
                'integer',
                'between:0,2147483647',
            ],
            'aim_natural_regeneration_hectares' => [
                'nullable',
                'integer',
                'between:0,2147483647',
            ],
            'aim_soil_condition' => [
                'nullable',
                'string',
                'in:severely_degraded,poor,fair,good,no_degradation',
            ],
            'aim_number_of_mature_trees' => [
                'nullable',
                'integer',
                'between:0,2147483647',
            ],
        ];
    }
}
