<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSatelliteMapRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'monitoring_id' => [
                'required',
                'integer',
                'exists:monitorings,id',
            ],
            'map' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
            'alt_text' => [
                'required',
                'string',
                'between:1,255',
            ],
        ];
    }
}
