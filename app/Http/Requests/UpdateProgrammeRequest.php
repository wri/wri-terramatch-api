<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgrammeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => 'string|between:1,255',
            'country' => 'string',
            'continent' => 'string',
            'start_date' => 'date',
            'end_date' => 'date',
            'thumbnail' => 'integer|exists:uploads,id',
            'additional_tree_species' => 'sometimes|nullable|integer',
            'boundary_geojson' => 'sometimes|string|json|geo_json|between:1,4294967295',
        ];
    }
}
