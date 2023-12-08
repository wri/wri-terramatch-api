<?php

namespace App\Validators\Extensions;

class FileExtensionIsCsv extends Extension
{
    public static $name = 'file_extension_is_csv';

    public static $message = [
        'FILE_EXTENSION_IS_CSV',
        'The {{attribute}} must be a csv.',
        ['attribute' => ':attribute'],
        'The :attribute must be a csv.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $fileHasExtension = strrpos($value, '.');

        if (! $fileHasExtension) {
            return false;
        }

        return substr($value, $fileHasExtension + 1) === 'csv';
    }
}
