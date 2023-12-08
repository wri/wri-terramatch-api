<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOfferContactsRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'offer_id' => [
                'required',
                'integer',
                'exists:offers,id',
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
