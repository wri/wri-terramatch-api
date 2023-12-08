<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMonitoringsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'match_id' => [
                'required',
                'integer',
                'exists:matches,id',
                'unique:monitorings,match_id',
            ],
        ];
    }
}
