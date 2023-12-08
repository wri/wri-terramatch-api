<?php

namespace App\Validators\Extensions;

class OrganisationFileType extends Extension
{
    public static $name = 'organisation_file_type';

    public static $message = [
        'ORGANISATION_FILE_TYPE',
        'The {{attribute}} field must be an organisation file type.',
        ['attribute' => ':attribute'],
        'The :attribute field must be an organisation file type.',
    ];

    public static function passes($attribute, $value, $parameters, $validator): bool
    {
        $organisationTypes = array_unique(array_values(config('data.organisation_file_types')));

        return in_array($value, $organisationTypes);
    }
}
