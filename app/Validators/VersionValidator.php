<?php

namespace App\Validators;

class VersionValidator extends Validator
{
    public const REJECT = [
        'rejected_reason' => 'required|string|rejected_reason',
        'rejected_reason_body' => 'required|string|between:1,65535',
    ];
}
