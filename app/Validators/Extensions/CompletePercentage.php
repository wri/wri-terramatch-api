<?php

namespace App\Validators\Extensions;

class CompletePercentage extends Extension
{
    public static $name = 'complete_percentage';

    public static $message = [
        'COMPLETE_PERCENTAGE',
        'The {{attribute}} field must be a complete percentage.',
        ['attribute' => ':attribute'],
        'The :attribute field must be a complete percentage.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        return array_sum(array_column($value, 'percentage')) == 100;
    }
}
