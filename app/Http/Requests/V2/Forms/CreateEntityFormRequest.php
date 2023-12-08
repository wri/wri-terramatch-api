<?php

namespace App\Http\Requests\V2\Forms;

use Illuminate\Foundation\Http\FormRequest;

class CreateEntityFormRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'parent_entity' => ['required', 'string'],
            'parent_uuid' => ['required', 'string'],
            'form_uuid' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
