<?php

namespace App\Http\Requests\EditHistory;

use Illuminate\Foundation\Http\FormRequest;

class EditHistoryCreateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'editable_type' => [
                'required',
                'string',
            ],
            'editable_id' => [
                'required',
                'integer',
            ],
            'content' => [
                'required',
                'nullable',
                'string',
            ],
        ];
    }
}
