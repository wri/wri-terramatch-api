<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgrammeTreeSpeciesRequest extends FormRequest
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
            ],
            'programme_id' => [
                'required',
                'integer',
                'exists:programmes,id',
            ],
            'programme_submission_id' => [
                'integer',
                'exists:submissions,id',
            ],
            'amount' => [
                'required_with:programme_submission_id',
                'integer',
                'between:0,2147483647',
            ],
        ];
    }
}
