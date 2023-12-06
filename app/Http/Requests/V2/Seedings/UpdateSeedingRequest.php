<?php

namespace App\Http\Requests\V2\Seedings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeedingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'sometimes|nullable|string',
            'weight_of_sample' => 'sometimes|nullable|integer',
            'seeds_in_sample' => 'sometimes|nullable|integer',
            'amount' => 'sometimes|nullable|integer',
        ];
    }
}
