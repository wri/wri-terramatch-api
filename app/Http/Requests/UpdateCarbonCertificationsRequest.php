<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCarbonCertificationsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'type' => ['string', 'in:' . implode(',', array_unique(array_values(config('data.carbon_certification_types'))))],
            'other_value' => ['required_if:type,other_value'],
            'link' => ['string', 'url', 'between:1,255'],
        ];
    }
}
