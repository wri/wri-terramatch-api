<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrafundSiteSubmissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'terrafund_site_id' => [
                'required',
                'integer',
                'exists:terrafund_sites,id',
            ],
            'shared_drive_link' => [
                'nullable',
                'url',
                'max:255',
            ],
            'terrafund_due_submission_id' => [
                '',
            ],
        ];
    }
}
