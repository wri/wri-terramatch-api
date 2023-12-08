<?php

namespace App\Http\Requests\EditHistory;

use Illuminate\Foundation\Http\FormRequest;

class EditHistoryApproveRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'uuid' => [
                'required',
                'string',
            ],
        ];
    }
}
