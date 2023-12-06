<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePitchContactRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'pitch_id' => [
                'required',
                'integer',
                'exists:pitches,id',
            ],
            'team_member_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:team_members,id',
            ],
            'user_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:users,id',
            ],
        ];
    }
}
