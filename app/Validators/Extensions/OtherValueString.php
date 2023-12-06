<?php

namespace App\Validators\Extensions;

class OtherValueString extends Extension
{
    public static $name = 'other_value_string';

    public static $message = [
        'OTHER_VALUE_STRING',
        'The {{attribute}} must be a string when type is other.',
        ['attribute' => ':attribute'],
        'The :attribute must be a string when type is other.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $data = $validator->getData();
        if ($data['type'] == 'other') {
            return is_string($value) && strlen($value) > 0;
        } else {
            return true;
        }
    }
}
