<?php

namespace App\Validators\Extensions;

use Illuminate\Support\Facades\Config;

class RevenueDriver extends Extension
{
    public static $name = "revenue_driver";
    public static $message = [
        "REVENUE_DRIVER",
        "The {{attribute}} field must contain revenue drivers.",
        ["attribute" => ":attribute"],
        "The :attribute field must contain revenue drivers."
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $revenueDrivers = array_unique(array_values(Config::get("data.revenue_drivers")));
        return in_array($value, $revenueDrivers);
    }
}
