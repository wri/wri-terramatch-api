<?php

namespace App\Http\Requests\V2\Organisations;

use Illuminate\Foundation\Http\FormRequest;

class JoinExistingOrganisationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'organisation_uuid' => [
                'required',
                'string',
            ],
        ];
    }
}
