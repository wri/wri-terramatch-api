<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTerrafundNurseryRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => [
                'string',
                'between:1,255',
            ],
            'start_date' => [
                'date_format:Y-m-d',
                'before:end_date',
            ],
            'end_date' => [
                'date_format:Y-m-d',
                'after:start_date',
            ],
            'seedling_grown' => [
                'integer',
                'min:0',
                'max:2147483647',
            ],
            'planting_contribution' => [
                'string',
                'between:1,65000',
            ],
            'nursery_type' => ['string', 'in:' . implode(',', array_unique(array_values(config('data.terrafund.nursery.type'))))],
        ];
    }
}
