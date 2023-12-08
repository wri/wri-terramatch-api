<?php

namespace App\Validators;

class SiteTreeSpeciesCsvValidator extends Validator
{
    public const CREATE = [
        'file' => 'required|file_extension_is_csv|max:2048',
        'site_submission_id' => 'integer|exists:site_submissions,id',
    ];
    public const CREATE_WITH_UPLOAD_ID = [
        'upload_id' => 'required|integer|exists:uploads,id',
        'site_submission_id' => 'integer|exists:site_submissions,id',
    ];
}
