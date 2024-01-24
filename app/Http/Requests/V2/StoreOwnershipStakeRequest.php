<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class StoreOwnershipStakeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => ['sometimes', 'string', 'nullable'],
            'last_name' => ['sometimes', 'string', 'nullable'],
            'organisation_id' => ['sometimes', 'string', 'exists:organisations,uuid'],
            'title' => ['sometimes', 'string', 'nullable'],
            'gender' => ['sometimes', 'string', 'min:1', 'max:65335'],
            'percent_ownership' => ['sometimes', 'numeric', 'between:0,100'],
            'year_of_birth' => ['sometimes', 'integer', 'min:1', 'max:3000'],
        ];
    }
}
