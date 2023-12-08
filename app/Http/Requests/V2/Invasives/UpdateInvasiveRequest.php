<?php

namespace App\Http\Requests\V2\Invasives;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvasiveRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'collection' => 'sometimes|nullable|string',
            'type' => 'sometimes|nullable|string',
            'name' => 'sometimes|nullable|string',
        ];
    }
}
