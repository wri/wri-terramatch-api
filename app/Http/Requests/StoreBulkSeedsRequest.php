<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBulkSeedsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'collection' => [
                'required',
                'array',
            ],
            'collection.*.name' => [
                'required',
                'string',
                'between:1,255',
            ],
            'collection.*.weight_of_sample' => [
                'required',
                'numeric',
                'between:0,100',
            ],
            'collection.*.seeds_in_sample' => [
                'required',
                'numeric',
                'between:1,100000',
            ],
        ];
    }
}
