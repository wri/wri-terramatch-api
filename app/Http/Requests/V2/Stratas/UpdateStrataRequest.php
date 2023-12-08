<?php

namespace App\Http\Requests\V2\Stratas;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStrataRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'description' => 'sometimes|nullable|string',
            'extent' => 'sometimes|nullable|integer|between:0,100',
        ];
    }
}
