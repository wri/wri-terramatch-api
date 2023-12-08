<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTerrafundDisturbanceRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'type' => ['string','max:255', 'in:' . implode(',', array_unique(array_values(config('data.terrafund.disturbances'))))],
            'description' => [
                'string',
                'max:65000',
            ],
        ];
    }
}
