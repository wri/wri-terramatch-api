<?php

namespace App\Http\Requests\V2\Projects;

use Illuminate\Foundation\Http\FormRequest;

class CreateProjectInviteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email_address' => 'required|string|email|between:1,255',
            'callback_url' => 'sometimes|nullable|string',
        ];
    }
}
