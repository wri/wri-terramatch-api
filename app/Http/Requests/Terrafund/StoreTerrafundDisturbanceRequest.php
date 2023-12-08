<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrafundDisturbanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'disturbanceable_type' => [
                'required',
                'string',
                'in:site_submission',
            ],
            'disturbanceable_id' => [
                'required',
                'integer',
            ],
            'type' => ['required','string','max:255', 'in:' . implode(',', array_unique(array_values(config('data.terrafund.disturbances'))))],
            'description' => [
                'required',
                'string',
                'max:65000',
            ],
        ];
    }
}
