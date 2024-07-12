<?php

namespace App\Http\Requests\V2\Projects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Foundation\Validation\ValidatesRequests;

class AddProjectManagerRequest extends FormRequest
{
    use ValidatesRequests;

    public function rules()
    {
        return [
            'email_address' => 'required|email|string',
        ];
    }
}
