<?php

namespace App\Validators\Extensions;

class FundingSource extends Extension
{
    public static $name = 'funding_source';

    public static $message = [
        'FUNDING_SOURCE',
        'The {{attribute}} field must contain funding sources.',
        ['attribute' => ':attribute'],
        'The :attribute field must contain funding sources.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $fundingSources = array_unique(array_values(config('data.funding_sources')));

        return in_array($value, $fundingSources);
    }
}
