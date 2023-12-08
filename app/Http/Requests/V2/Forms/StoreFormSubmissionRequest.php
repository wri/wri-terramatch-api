<?php

namespace App\Http\Requests\V2\Forms;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormSubmissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'form_uuid' => ['required', 'string', 'exists:forms,uuid'],
            'project_pitch_uuid' => ['sometimes', 'nullable', 'string', 'exists:project_pitches,uuid'],
            'application_uuid' => ['sometimes', 'nullable', 'string', 'exists:applications,uuid'],
        ];
    }
}
