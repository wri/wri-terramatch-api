<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTerrafundNurserySubmissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'seedlings_young_trees' => [
                'integer',
                'min:0',
                'max:2147483647',
            ],
            'interesting_facts' => [
                'string',
                'between:1,65000',
            ],
            'site_prep' => [
                'string',
                'between:1,65000',
            ],
            'shared_drive_link' => [
                'nullable',
                'url',
                'max:255',
            ],
        ];
    }
}
