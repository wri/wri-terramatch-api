<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class AcceptTerrafundProgrammeInviteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'token' => [
                'required',
                'string',
                'exists:terrafund_programme_invites,token',
            ],
        ];
    }
}
