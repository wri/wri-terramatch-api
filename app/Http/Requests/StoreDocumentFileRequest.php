<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentFileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'document_fileable_type' => [
                'required',
                'string',
            ],
            'document_fileable_id' => [
                'required',
                'integer',
            ],
            'title' => [
                'nullable',
                'string',
            ],
            'collection' => [
                'nullable',
                'string',
            ],
            'upload' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
            'is_public' => [
                'required',
                'boolean',
            ],
        ];
    }
}
