<?php

namespace App\Http\Requests\V2\File;

use Illuminate\Foundation\Http\FormRequest;

class BulkUploadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            '*.uuid' => [
                'prohibited',
            ],
            '*.download_url' => [
                'required',
                'url',
            ],
            '*.title' => [
                'sometimes',
                'nullable',
                'string',
            ],
            '*.lat' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            '*.lng' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            '*.is_public' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
        ];
    }
}
