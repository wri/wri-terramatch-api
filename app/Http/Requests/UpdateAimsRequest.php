<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAimsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'year_five_trees' => [
                'required',
                'integer',
                'between:1,100000000',
            ],
            'restoration_hectares' => [
                'required',
                'integer',
                'between:1,2147483647',
            ],
            'survival_rate' => [
                'nullable',
                'integer',
                'between:0,100',
            ],
            'year_five_crown_cover' => [
                'required',
                'integer',
                'between:1,2147483647',
            ],
        ];
    }
}
