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
        $allowedMimeTypes = ['image/jpeg', 'image/gif', 'image/png', 'application/pdf', 'image/tiff', 'image/svg+xml'];

        return $file && in_array($file->getClientMimeType(), $allowedMimeTypes);
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
