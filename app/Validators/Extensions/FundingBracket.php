<?php

namespace App\Validators\Extensions;

class FundingBracket extends Extension
{
    public static $name = 'funding_bracket';

    public static $message = [
        'FUNDING_BRACKET',
        'The {{attribute}} field must be a funding bracket.',
        ['attribute' => ':attribute'],
        'The :attribute field must be a funding bracket.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $fundingBrackets = array_unique(array_values(config('data.funding_brackets')));

        return in_array($value, $fundingBrackets);
    }
}
