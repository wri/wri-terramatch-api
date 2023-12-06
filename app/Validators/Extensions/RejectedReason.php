<?php

namespace App\Validators\Extensions;

class RejectedReason extends Extension
{
    public static $name = 'rejected_reason';

    public static $message = [
        'REJECTED_REASON',
        'The {{attribute}} field must be a rejected reason.',
        ['attribute' => ':attribute'],
        'The :attribute field must be a rejected reason.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $rejectedReasons = array_unique(array_values(config('data.rejected_reasons')));

        return in_array($value, $rejectedReasons);
    }
}
