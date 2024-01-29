<?php

namespace App\Http\Requests\V2\ReportingFrameworks;

use Illuminate\Foundation\Http\FormRequest;

class CreateReportingFrameworkRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['sometimes', 'string', 'nullable'],
            'access_code' => ['sometimes', 'string', 'nullable'],
            'project_form_uuid' => ['sometimes', 'string', 'nullable'],
            'project_report_form_uuid' => ['sometimes', 'string', 'nullable'],
            'site_form_uuid' => ['sometimes', 'string', 'nullable'],
            'site_report_form_uuid' => ['sometimes', 'string', 'nullable'],
            'nursery_form_uuid' => ['sometimes', 'string', 'nullable'],
            'nursery_report_form_uuid' => ['sometimes', 'string', 'nullable'],
        ];
    }
}
