<?php

namespace App\Http\Requests\V2\Forms;

use Illuminate\Foundation\Http\FormRequest;

class ExportFormSubmissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            // 'uuids' => ['required', 'array'],
            // 'uuids.*' => ['required', 'string', 'exists:form_submissions,uuid'],
        ];
    }
}
