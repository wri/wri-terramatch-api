<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgrammeBoundaryRequest extends FormRequest
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
            'boundary_geojson' => [
                'sometimes',
                'string',
                'json',
                'between:1,4294967295',
            ],
        ];
    }
}
