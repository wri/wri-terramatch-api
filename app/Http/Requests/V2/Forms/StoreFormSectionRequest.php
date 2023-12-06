<?php

namespace App\Http\Requests\V2\Forms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFormSectionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'order' => ['required', 'integer', 'min:1', 'max:255', Rule::unique('form_sections', 'order')->where('form_id', $this->form_id)],
            'form_id' => ['required', 'string', 'exists:forms,uuid'],
        ];
    }
}
