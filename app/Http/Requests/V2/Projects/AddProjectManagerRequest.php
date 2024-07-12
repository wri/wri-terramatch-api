<?php

namespace App\Http\Requests\V2\Projects;

use Illuminate\Foundation\Http\FormRequest;

class AddProjectManagerRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email_address' => 'required|email|string',
        ];
    }
}
