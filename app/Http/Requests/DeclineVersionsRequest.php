<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeclineVersionsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'rejected_reason' => ['required', 'string', 'in:' . implode(',', array_unique(array_values(config('data.rejected_reasons'))))],
            'rejected_reason_body' => ['required', 'string', 'between:1,65535'],
        ];
    }
}
