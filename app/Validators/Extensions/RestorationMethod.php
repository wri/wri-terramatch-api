<?php

namespace App\Validators\Extensions;

class RestorationMethod extends Extension
{
    public static $name = 'restoration_method';

    public static $message = [
        'RESTORATION_METHOD',
        'The {{attribute}} field must contain restoration methods.',
        ['attribute' => ':attribute'],
        'The :attribute field must contain restoration methods.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $restorationMethods = array_unique(array_values(config('data.restoration_methods')));

        return in_array($value, $restorationMethods);
    }
}
