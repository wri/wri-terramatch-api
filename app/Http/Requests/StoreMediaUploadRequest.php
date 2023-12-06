<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMediaUploadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'media_title' => [
                'string',
                'between:1,255',
            ],
            'is_public' => [
                'boolean',
            ],
            'programme_id' => [
                'integer',
                'exists:programmes,id',
                'required_without:site_id',
            ],
            'site_id' => [
                'integer',
                'exists:sites,id',
                'required_without:programme_id',
            ],
            'upload' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
            'location_long' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'location_lat' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
        ];
    }
}
