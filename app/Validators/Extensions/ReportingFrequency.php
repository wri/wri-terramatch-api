<?php

namespace App\Validators\Extensions;

class ReportingFrequency extends Extension
{
    public static $name = 'reporting_frequency';

    public static $message = [
        'REPORTING_FREQUENCY',
        'The {{attribute}} field must be a reporting frequency.',
        ['attribute' => ':attribute'],
        'The :attribute field must be a reporting frequency.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $reportingFrequencies = array_unique(array_values(config('data.reporting_frequencies')));

        return in_array($value, $reportingFrequencies);
    }
}
