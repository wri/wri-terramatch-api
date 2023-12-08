<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTerrafundSiteSubmissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'shared_drive_link' => [
                'nullable',
                'url',
                'max:255',
            ],
        ];
    }
}
