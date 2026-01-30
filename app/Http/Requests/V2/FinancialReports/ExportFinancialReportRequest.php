<?php

namespace App\Http\Requests\V2\FinancialReports;

use Illuminate\Foundation\Http\FormRequest;

class ExportFinancialReportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            // 'uuids' => ['required', 'array'],
            // 'uuids.*' => ['required', 'string', 'exists:project_pitches,uuid'],
        ];
    }
}
