<?php

namespace App\Validators;

class PitchDocumentValidator extends Validator
{
    public const CREATE = [
        "pitch_id" => "required|integer|exists:pitches,id",
        "name" => "required|string|between:1,255",
        "type" => "required|string|document_type",
        "document" => "required|integer|exists:uploads,id"
    ];

    public const UPDATE = [
        "name" => "sometimes|required|string|between:1,255",
        "type" => "sometimes|required|string|document_type",
        "document" => "sometimes|required|integer|exists:uploads,id"
    ];
}