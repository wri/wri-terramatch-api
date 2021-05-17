<?php

namespace App\Validators;

class InterestValidator extends Validator
{
    public const CREATE = [
        "initiator" => "required|string|in:offer,pitch",
        "offer_id" => "required|integer|exists:offers,id",
        "pitch_id" => "required|integer|exists:pitches,id"
    ];
}