<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class ProjectPipelineRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => ['sometimes', 'string', 'nullable', 'max:256'],
            'submitted_by' => ['sometimes', 'string', 'nullable', 'max:256'],
            'description' => ['sometimes', 'string', 'nullable', 'max:500'],
            'program' => ['sometimes', 'string', 'nullable', 'max:256'],
            'cohort' => ['sometimes', 'string', 'nullable', 'max:256'],
            'publish_for' => ['sometimes', 'string', 'nullable', 'max:256'],
            'url' => ['sometimes', 'string', 'nullable', 'max:256'],
        ];
    }
}
