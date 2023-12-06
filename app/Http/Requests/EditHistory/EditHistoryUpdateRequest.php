<?php

namespace App\Http\Requests\EditHistory;

use Illuminate\Foundation\Http\FormRequest;

class EditHistoryUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'content' => [
                'required',
                'nullable',
                'string',
            ],
            'comments' => [
                'sometimes',
                'nullable',
                'string',
            ],
        ];
    }
}
