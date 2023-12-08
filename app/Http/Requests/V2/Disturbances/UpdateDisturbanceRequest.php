<?php

namespace App\Http\Requests\V2\Disturbances;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDisturbanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'sometimes|nullable|string',
            'description' => 'sometimes|nullable|string',
            'intensity' => 'sometimes|nullable|string',
            'extent' => 'sometimes|nullable|string',
            'collection' => 'sometimes|nullable|string',
        ];
    }
}
