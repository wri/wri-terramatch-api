<?php

namespace App\Validators;

class PitchContactValidator extends Validator
{
    public $create = [
        "pitch_id" => "required|integer|exists:pitches,id",
        "team_member_id" => "required_without:user_id|integer|exists:team_members,id",
        "user_id" => "required_without:team_member_id|integer|exists:users,id"
    ];
}