<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganisationDocumentsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'between:1,255'],
            'type' => ['required', 'string', 'in:' . implode(',', array_unique(array_values(config('data.document_types'))))],
            'document' => ['required', 'integer', 'exists:uploads,id'],
        ];
    }
}
