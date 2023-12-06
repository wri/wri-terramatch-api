<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSiteSubmissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'technical_narrative' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'public_narrative' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'disturbance_information' => [
                'string',
                'max:4294967295',
            ],
            'direct_seeding_kg' => [
                'sometimes',
                'integer',
                'between:0,2147483648',
            ],
            'created_by' => [
                'string',
                'between:1,255',
            ],
            'additional_tree_species' => [
                'sometimes',
                'nullable',
                'integer',
            ],
            'workdays_paid' => [
                'sometimes',
                'nullable',
                'integer',
                'max:99999',
            ],
            'workdays_volunteer' => [
                'sometimes',
                'nullable',
                'integer',
                'max:99999',
            ],
        ];
    }
}
