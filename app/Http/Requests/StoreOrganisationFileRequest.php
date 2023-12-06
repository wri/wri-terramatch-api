<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganisationFileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'upload' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
            'type' => [
                'required',
                'string',
            ],
            'organisation_id' => [
                'required',
                'integer',
                'exists:organisations,id',
            ],
        ];
    }
}
