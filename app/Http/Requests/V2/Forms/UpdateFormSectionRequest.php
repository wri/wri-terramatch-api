<?php

namespace App\Http\Requests\V2\Forms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormSectionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'order' => ['integer', 'min:1', 'max:255', Rule::unique('form_sections', 'order')->ignore($this->id)->where('form_id', $this->form_id)],
        ];
    }
}
