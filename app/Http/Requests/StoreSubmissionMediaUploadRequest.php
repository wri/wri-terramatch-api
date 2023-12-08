<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubmissionMediaUploadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'media_title' => [
                'string',
                'between:1,255',
            ],
            'is_public' => [
                'present',
                'boolean',
            ],
            'site_submission_id' => [
                'required_without:submission_id',
                'integer',
                'exists:site_submissions,id',
            ],
            'submission_id' => [
                'required_without:site_submission_id',
                'integer',
                'exists:submissions,id',
            ],
            'upload' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
            'location_long' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'location_lat' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
        ];
    }
}
