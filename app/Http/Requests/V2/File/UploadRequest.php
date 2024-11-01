<?php

namespace App\Http\Requests\V2\File;

use App\Rules\CheckMimeTypeRule;
use Illuminate\Foundation\Http\FormRequest;

class UploadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'uuid' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'upload_file' => [
                'sometimes',
                'mimes:csv,txt,xls,xlsx,jpg,gif,png,pdf,tiff,svg,mp4',
            ],
            'collection' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'title' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'lat' => [
                new CheckMimeTypeRule(),
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'lng' => [
                new CheckMimeTypeRule(),
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'is_public' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
        ];
    }
}
