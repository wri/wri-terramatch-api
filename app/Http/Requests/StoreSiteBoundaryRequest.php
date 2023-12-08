<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteBoundaryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'site_id' => [
                'required',
                'integer',
                'exists:sites,id',
            ],
            'boundary_geojson' => [
                'required',
                'string',
                'json',
                'between:1,4294967295',
            ],
        ];
    }
}
