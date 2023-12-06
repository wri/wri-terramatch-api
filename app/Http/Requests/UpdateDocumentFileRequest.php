<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentFileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'title' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'collection' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'is_public' => [
                'sometimes',
                'required',
                'boolean',
            ],
        ];
    }
}
