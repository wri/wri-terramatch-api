<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCarbonCertificationsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'pitch_id' => ['required', 'integer', 'exists:pitches,id'],
            'type' => ['required', 'string', 'in:' . implode(',', array_unique(array_values(config('data.carbon_certification_types'))))],
            'other_value' => ['required_if:type,other_value'],
            'link' => ['required', 'string', 'url', 'between:1,255'],
        ];
    }
}
