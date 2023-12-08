<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkTerrafundFileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'data' => [
                'required',
                'array',
                'min:10',
            ],
            'data.*.fileable_type' => [
                'required',
                'string',
                'in:site_submission',
            ],
            'data.*.fileable_id' => [
                'required',
                'integer',
            ],
            'data.*.upload' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
            'data.*.is_public' => [
                'required',
                'boolean',
            ],
            'data.*.location_long' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'data.*.location_lat' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
        ];
    }
}
