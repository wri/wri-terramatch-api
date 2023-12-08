<?php

namespace App\Http\Requests\V2\ProjectPitches;

use Illuminate\Foundation\Http\FormRequest;

class ExportProjectPitchRequest extends FormRequest
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
