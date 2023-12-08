<?php

namespace App\Http\Requests\V2\Forms;

use App\Models\V2\Forms\Form;
use Illuminate\Foundation\Http\FormRequest;

class LinkedFieldListingRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'form_types' => ['sometimes', 'array'],
            'form_types.*' => ['sometimes', 'string', 'in:' . implode(',', array_keys(Form::$types))],
        ];
    }
}
