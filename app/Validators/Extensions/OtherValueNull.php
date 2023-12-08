<?php

namespace App\Validators\Extensions;

class OtherValueNull extends Extension
{
    public static $name = 'other_value_null';

    public static $message = [
        'OTHER_VALUE_NULL',
        'The {{attribute}} must be null when type is not other.',
        ['attribute' => ':attribute'],
        'The :attribute must be null when type is not other.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $data = $validator->getData();
        if ($data['type'] != 'other') {
            return is_null($value);
        } else {
            return true;
        }
    }
}
