<?php

namespace App\Http\Requests\V2\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBannerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'banners' => ['required', 'json', 'min:1', 'max:65000'],
        ];
    }
}
