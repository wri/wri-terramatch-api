<?php

namespace App\Validators;

class UploadValidator extends Validator
{
    public const CREATE = [
        "upload" => "required|file|mimetypes:image/jpeg,image/png,image/gif,video/mp4,video/quicktime,video/3gpp,application/pdf,image/tiff"
    ];
}