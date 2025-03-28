<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class CheckMimeTypeRule implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $file = request()->file('upload_file');
        if (! $file) {
            return false;
        }

        $allowedMimeTypes = [
            'image/jpeg',
            'image/gif',
            'image/png',
            'application/pdf',
            'image/tiff',
            'image/svg+xml',
            'image/heif',
            'image/heic',
            'application/octet-stream',
        ];

        $isAllowedMimeType = in_array($file->getClientMimeType(), $allowedMimeTypes);

        if ($file->getClientMimeType() === 'application/octet-stream') {
            $extension = strtolower($file->getClientOriginalExtension());

            return in_array($extension, ['heic', 'heif']);
        }

        return $isAllowedMimeType;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute field is not required if file is not an image file type.';
    }
}
