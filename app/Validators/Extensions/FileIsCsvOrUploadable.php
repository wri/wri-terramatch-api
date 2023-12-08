<?php

namespace App\Validators\Extensions;

class FileIsCsvOrUploadable extends Extension
{
    public static $name = 'file_is_csv_or_uploadable';

    public static $message = [
        'FILE_IS_CSV_OR_UPLOADABLE',
        'The {{attribute}} must be a csv, jpeg, png, gif, mp4, mov, quicktime,  3gpp, pdf, tiff, xls or xlsx.',
        ['attribute' => ':attribute'],
        'The :attribute must be a csv, jpeg, png, gif, mp4, mov, quicktime,  3gpp, pdf, tiff, xls or xlsx.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        return $value->getClientOriginalExtension() == 'csv' || 'jpeg' || 'png' || 'gif' || 'mp4' || 'mov' || '3gp' || 'pdf' || 'tiff' || 'xlsx' || 'xls' || 'vnd.openxmlformats-officedocument.spreadsheetml.sheet' || 'application/vnd.ms-excel';
    }
}
