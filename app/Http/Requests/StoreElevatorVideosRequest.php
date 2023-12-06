<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreElevatorVideosRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'introduction' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
            'aims' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
            'importance' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
        ];
    }
}
