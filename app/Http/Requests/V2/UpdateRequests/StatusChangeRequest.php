<?php

namespace App\Http\Requests\V2\UpdateRequests;

use Illuminate\Foundation\Http\FormRequest;

class StatusChangeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'feedback' => ['sometimes', 'string', 'nullable'],
            'feedback_fields' => ['sometimes', 'array', 'nullable'],
        ];
    }
}
