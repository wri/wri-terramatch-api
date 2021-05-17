<?php

namespace App\Validators;

class PitchContactValidator extends Validator
{
    public const CREATE = [
        "pitch_id" => "required|integer|exists:pitches,id",
        "team_member_id" => "sometimes|required|integer|exists:team_members,id",
        "user_id" => "sometimes|required|integer|exists:users,id"
    ];
}