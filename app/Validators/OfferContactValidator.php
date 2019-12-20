<?php

namespace App\Validators;

class OfferContactValidator extends Validator
{
    public $create = [
        "offer_id" => "required|integer|exists:offers,id",
        "team_member_id" => "required_without:user_id|integer|exists:team_members,id",
        "user_id" => "required_without:team_member_id|integer|exists:users,id"
    ];
}