<?php

namespace App\Validators\Extensions;

class RevenueDriver extends Extension
{
    public static $name = 'revenue_driver';

    public static $message = [
        'REVENUE_DRIVER',
        'The {{attribute}} field must contain revenue drivers.',
        ['attribute' => ':attribute'],
        'The :attribute field must contain revenue drivers.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $revenueDrivers = array_unique(array_values(config('data.revenue_drivers')));

        return in_array($value, $revenueDrivers);
    }
}
