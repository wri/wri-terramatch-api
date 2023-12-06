<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFundingTypeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'source' => ['sometimes', 'string', 'nullable'],
            'amount' => ['sometimes', 'integer', 'min:1', 'max:4294967295'],
            'year' => ['sometimes', 'integer', 'min:1', 'max:3000'],
            'type' => ['sometimes', 'string'],
        ];
    }
}
