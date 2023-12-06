<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrganisationPhotoRequest extends FormRequest
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
            'is_public' => [
                'required',
                'boolean',
            ],
            'organisation_id' => [
                'required',
                'integer',
                'exists:organisations,id',
            ],
        ];
    }
}
