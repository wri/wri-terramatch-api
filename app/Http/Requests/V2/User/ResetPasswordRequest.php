<?php

namespace App\Http\Requests\V2\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ResetPasswordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'password' => ['required', 'string', Password::min(10)->mixedCase()->numbers()],
        ];
    }
}
