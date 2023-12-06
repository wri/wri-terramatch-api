<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeedsRequest extends FormRequest
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
            'weight_of_sample' => [
                'required',
                'numeric',
                'between:0,100',
            ],
            'seeds_in_sample' => [
                'required',
                'numeric',
                'between:1,100000',
            ],
        ];
    }
}
