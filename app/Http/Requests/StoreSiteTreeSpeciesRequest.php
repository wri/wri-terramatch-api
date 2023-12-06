<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteTreeSpeciesRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'site_id' => [
                'required',
                'integer',
                'exists:sites,id',
            ],
            'site_submission_id' => [
                'integer',
                'exists:site_submissions,id',
            ],
            'name' => [
                'required',
                'string',
                'between:1,255',
            ],
            'amount' => [
                'required',
                'integer',
                'between:0,2147483647',
            ],
        ];
    }
}
