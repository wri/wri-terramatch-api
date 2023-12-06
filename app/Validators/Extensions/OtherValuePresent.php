<?php

namespace App\Validators\Extensions;

class OtherValuePresent extends Extension
{
    public static $name = 'other_value_present';

    public static $message = [
        'OTHER_VALUE_PRESENT',
        'The {{attribute}} field is required when type is present.',
        ['attribute' => ':attribute'],
        'The :attribute field is required when type is present.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $data = $validator->getData();
        if (array_key_exists('type', $data)) {
            return array_key_exists('other_value', $data);
        } else {
            return true;
        }
    }
}
