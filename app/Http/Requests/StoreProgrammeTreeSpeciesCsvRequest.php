<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgrammeTreeSpeciesCsvRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'upload_id' => [
                'integer',
                'exists:uploads,id',
                'required_without:file',
            ],
            'file' => [
                'max:2048',
                'required_without:upload_id',
            ],
            'programme_id' => [
                'sometimes',
            ],

            'programme_submission_id' => [
                'integer',
                'exists:submissions,id',
            ],
        ];
    }
}
