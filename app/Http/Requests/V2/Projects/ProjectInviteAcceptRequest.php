<?php

namespace App\Http\Requests\V2\Projects;

use Illuminate\Foundation\Http\FormRequest;

class ProjectInviteAcceptRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'token' => 'required|string',
        ];
    }
}
