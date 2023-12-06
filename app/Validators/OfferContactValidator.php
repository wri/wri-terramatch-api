<?php

namespace App\Validators;

class OfferContactValidator extends Validator
{
    public const CREATE = [
        'offer_id' => 'required|integer|exists:offers,id',
        'team_member_id' => 'sometimes|required|integer|exists:team_members,id',
        'user_id' => 'sometimes|required|integer|exists:users,id',
    ];
}
