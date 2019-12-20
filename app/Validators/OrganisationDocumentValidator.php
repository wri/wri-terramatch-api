<?php

namespace App\Validators;

class OrganisationDocumentValidator extends Validator
{
    public $create = [
        "name" => "required|string|between:1,255",
        "type" => "required|string|document_type",
        "document" => "required|integer|exists:uploads,id"
    ];

    public $update = [
        "name" => "sometimes|required|string|between:1,255",
        "type" => "sometimes|required|string|document_type",
        "document" => "sometimes|required|integer|exists:uploads,id"
    ];
}