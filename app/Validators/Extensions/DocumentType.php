<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class DocumentType extends Extension
{
    public static $name = "document_type";
    public static $message = [
        "DOCUMENT_TYPE",
        "The {{attribute}} field must be a document type.",
        ["attribute" => ":attribute"],
        "The :attribute field must be a document type."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $documentTypes = array_unique(array_values(Config::get("data.document_types")));
        return in_array($value, $documentTypes);
    }
}