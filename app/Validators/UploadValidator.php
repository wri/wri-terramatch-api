<?php

namespace App\Validators;

class UploadValidator extends Validator
{
    public $create = [
        "upload" => "required|file|mimetypes:image/jpeg,image/png,image/gif,video/mp4,application/pdf"
    ];

    public $createImage = [
        "upload" => "between:0,2000"
    ];

    public $createVideo = [
        "upload" => "between:0,8000"
    ];

    public $createDocument = [
        "upload" => "between:0,4000"
    ];
}