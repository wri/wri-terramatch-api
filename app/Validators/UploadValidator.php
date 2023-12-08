<?php

namespace App\Validators;

class UploadValidator extends Validator
{
    public const CREATE = [
        'upload' => 'required|file|file_is_csv_or_uploadable',
        'title' => 'sometimes|nullable|string|between:0,255',
    ];

    public const UPDATE = [
        'title' => 'required|string|between:0,255',
    ];
}
