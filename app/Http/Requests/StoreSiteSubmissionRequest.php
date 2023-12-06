<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteSubmissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'site_id' => [
                'required',
                'integer',
                'exists:sites,id',
            ],
            'site_submission_title' => [
                'nullable',
                'string',
                'between:1,255',
            ],
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
            'direct_seeding_kg' => [
                'sometimes',
                'nullable',
                'integer',
                'between:0,2147483648',
            ],
            'created_by' => [
                'required',
                'string',
                'between:1,255',
            ],
            'due_submission_id' => [
                'nullable',
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
