<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSocioeconomicBenefitsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'upload' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
            'name' => [
                'required',
                'string',
                'between:1,255',
            ],
            'programme_id' => [
                'integer',
                'exists:programmes,id',
            ],
            'programme_submission_id' => [
                'integer',
                'exists:submissions,id',
            ],
            'site_id' => [
                'integer',
                'exists:sites,id',
            ],
            'site_submission_id' => [
                'integer',
                'exists:site_submissions,id',
            ],
        ];
    }
}
