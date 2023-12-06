<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'programme_id' => [
                'required',
                'integer',
                'exists:programmes,id',
            ],
            'site_name' => [
                'required',
                'string',
                'between:1,255',
            ],
            'site_description' => [
                'required',
                'string',
                'between:1,240',
            ],
            'site_history' => [
                'nullable',
                'string',
                'between:1,500',
            ],
            'additional_tree_species' => [
                'sometimes',
                'nullable',
                'integer',
            ],
            'end_date' => [
                'required',
                'date',
            ],
            'planting_pattern' => [
                'sometimes',
                'nullable',
                'string',
                'between:0,65535',
            ],
            'stratification_for_heterogeneity' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:uploads,id',
            ],
            'control_site' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
        ];
    }
}
