<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialIndicatorsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => ['sometimes', 'integer', 'nullable'],
            'year' => ['sometimes', 'integer', 'nullable'],
            'documentation' => ['sometimes', 'string', 'nullable'],
            'description' => ['sometimes', 'string', 'nullable'],    
            'organisation_id' => ['sometimes', 'string', 'exists:organisations,uuid'],
        ];
    }
}
