<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterestRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'initiator' => [
                'required',
                'string',
                'in:offer,pitch',
            ],
            'offer_id' => [
                'required',
                'integer',
                'exists:offers,id',
            ],
            'pitch_id' => [
                'required',
                'integer',
                'exists:pitches,id',
            ],
        ];
    }
}
