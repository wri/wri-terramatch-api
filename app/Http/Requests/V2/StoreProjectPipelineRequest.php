<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectPipelineRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'Name' => ['sometimes', 'string', 'nullable', 'max:256'],
            'SubmittedBy' => ['sometimes', 'string', 'nullable', 'max:256'],
            'Description' => ['sometimes', 'string', 'nullable', 'max:500'],
            'Program' => ['sometimes', 'string', 'nullable', 'max:256'],
            'Cohort' => ['sometimes', 'string', 'nullable', 'max:256'],
            'PublishFor' => ['sometimes', 'string', 'nullable', 'max:256'],
            'URL' => ['sometimes', 'string', 'nullable', 'max:256'],
            'CreatedDate' => ['sometimes', 'date'],
            'ModifiedDate' => ['sometimes', 'date'],
        ];
    }
}