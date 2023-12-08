<?php

namespace App\Http\Requests\V2\Seedings;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeedingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'model_type' => 'required|string|in:site,site-report',
            'model_uuid' => 'required|string',
            'name' => 'sometimes|nullable|string',
            'weight_of_sample' => 'sometimes|nullable|between:0,100',
            'seeds_in_sample' => 'sometimes|nullable|between:1,100000',
            'amount' => 'sometimes|nullable|integer',
        ];
    }
}
