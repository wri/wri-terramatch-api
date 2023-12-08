<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePitchDocumentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
            'type' => [
                'sometimes',
                'required',
                'string',
            ],
            'document' => [
                'sometimes',
                'required',
                'integer',
                'exists:uploads,id',
            ],
        ];
    }
}
