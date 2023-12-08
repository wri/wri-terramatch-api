<?php

namespace App\Http\Requests\V2\Stages;

use Illuminate\Foundation\Http\FormRequest;

class StoreStageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'funding_programme_id' => ['sometimes', 'required', 'string', 'exists:funding_programmes,uuid'],
            'form_id' => ['sometimes', 'required', 'string', 'exists:forms,uuid'],
            'deadline_at' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i:s'],
            'order' => ['required', 'integer', 'min:1', 'max:255'],
        ];
    }
}
