<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveProgrammeInviteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'programme_id' => [
                'required',
                'integer',
                'exists:programmes,id',
            ],
        ];
    }
}
