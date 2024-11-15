<?php

namespace App\Http\Requests\V2\Projects;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateProjectRequest extends FormRequest
{
    public function rules()
    {
        return [
            'is_test' => 'sometimes|boolean',
        ];
    }
}
