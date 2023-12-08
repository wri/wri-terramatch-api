<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => 'string|between:1,255',
            'description' => 'string|between:1,240',
            'history' => 'nullable|string|between:1,500',
            'establishment_date' => 'sometimes|date',
            'end_date' => 'date',
            'additional_tree_species' => 'sometimes|nullable|integer',
            'planting_pattern' => 'sometimes|nullable|string|between:0,65535',
            'stratification_for_heterogeneity' => 'sometimes|nullable|integer|exists:uploads,id',
            'control_site' => 'sometimes|nullable|boolean',
            'boundary_geojson' => 'sometimes|nullable|string',
        ];
    }
}
