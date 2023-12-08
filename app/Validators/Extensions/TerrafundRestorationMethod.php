<?php

namespace App\Validators\Extensions;

class TerrafundRestorationMethod extends Extension
{
    public static $name = 'terrafund_restoration_method';

    public static $message = [
        'TERRAFUND_RESTORATION_METHOD',
        'The {{attribute}} field must contain restoration methods.',
        ['attribute' => ':attribute'],
        'The :attribute field must contain restoration methods.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $restorationMethods = array_unique(array_values(config('data.terrafund.site.restoration_methods')));

        return in_array($value, $restorationMethods);
    }
}
