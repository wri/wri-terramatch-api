<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganisationDocumentsRequest extends FormRequest
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
            'type' => ['sometimes', 'required', 'string', 'in:' . implode(',', array_unique(array_values(config('data.document_types'))))],
            'document' => [
                'sometimes',
                'required',
                'integer',
                'exists:uploads,id',
            ],
        ];
    }
}
