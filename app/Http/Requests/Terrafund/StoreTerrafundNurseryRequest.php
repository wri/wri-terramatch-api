<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrafundNurseryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'between:1,255',
            ],
            'start_date' => [
                'required',
                'date_format:Y-m-d',
                'before:end_date',
            ],
            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after:start_date',
            ],
            'seedling_grown' => [
                'required',
                'integer',
                'min:0',
                'max:2147483647',
            ],
            'planting_contribution' => [
                'required',
                'string',
                'between:1,65000',
            ],
            'nursery_type' => ['required', 'string', 'in:' . implode(',', array_unique(array_values(config('data.terrafund.nursery.type'))))],
        ];
    }
}
