<?php

namespace App\Http\Requests\V2\File;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'upload_file' => [
                'required',
                'mimes:csv,xlsx',
            ],
            'importable_type' => [
                'required',
                'string',
                Rule::in([
                    'programme',
                    'project',
                    'site',
                    'terrafund_programme',
                    'terrafund_project',
                    'terrafund_site',
                ]),
            ],
            'importable_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
