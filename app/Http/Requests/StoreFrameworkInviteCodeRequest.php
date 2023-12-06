<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFrameworkInviteCodeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'code' => [
                'required',
                'string',
                'between:1,20',
            ],
            'framework' => [
                'required',
                'string',
                'in:ppc,terrafund',
            ],
        ];
    }
}
