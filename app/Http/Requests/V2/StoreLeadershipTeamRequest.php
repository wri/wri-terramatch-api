<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadershipTeamRequest extends FormRequest
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
            'position' => ['sometimes', 'string', 'min:1', 'max:65335'],
            'gender' => ['sometimes', 'string', 'min:1', 'max:65335'],
            'age' => ['sometimes', 'integer', 'min:1', 'max:255'],
        ];
    }
}
