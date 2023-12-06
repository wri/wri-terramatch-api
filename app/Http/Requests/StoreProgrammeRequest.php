<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgrammeRequest extends FormRequest
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
            'country' => [
                'required',
                'string',
            ],
            'continent' => [
                'required',
                'string',
            ],
            'end_date' => [
                'required',
                'date',
            ],
            'thumbnail' => [
                'nullable',
                'integer',
                'exists:uploads,id',
            ],
            'additional_tree_species' => [
                'sometimes',
                'nullable',
                'integer',
            ],
        ];
    }
}
