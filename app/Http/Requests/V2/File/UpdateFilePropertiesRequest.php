<?php

namespace App\Http\Requests\V2\File;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFilePropertiesRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:256',
            'is_public' => 'sometimes|boolean',
        ];
    }
}
