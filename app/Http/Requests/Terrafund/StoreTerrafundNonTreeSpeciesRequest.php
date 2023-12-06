<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrafundNonTreeSpeciesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'speciesable_type' => [
                'required',
                'string',
                'in:site_submission',
            ],
            'speciesable_id' => [
                'required',
                'integer',
            ],
            'name' => [
                'required',
                'string',
                'between:1,255',
            ],
            'amount' => [
                'required',
                'integer',
                'between:0,2147483647',
            ],
        ];
    }
}
