<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrafundProgrammeInviteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'email_address' => [
                'required',
                'string',
                'email',
                'between:1,255',
            ],
        ];
    }
}
