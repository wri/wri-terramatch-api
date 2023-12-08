<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgrammeSubmissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'title' => [
                'string',
                'between:1,255',
            ],
            'technical_narrative' => [
                'string',
                'between:1,65535',
            ],
            'public_narrative' => [
                'sometimes',
                'nullable',
                'string',
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
