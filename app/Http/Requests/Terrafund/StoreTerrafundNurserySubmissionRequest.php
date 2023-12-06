<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrafundNurserySubmissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'seedlings_young_trees' => [
                'required',
                'integer',
                'min:0',
                'max:2147483647',
            ],
            'interesting_facts' => [
                'required',
                'string',
                'between:1,65000',
            ],
            'site_prep' => [
                'required',
                'string',
                'between:1,65000',
            ],
            'shared_drive_link' => [
                'nullable',
                'url',
                'max:255',
            ],
            'terrafund_nursery_id' => [
                'required',
                'integer',
                'exists:terrafund_nurseries,id',
            ],
            'terrafund_due_submission_id' => [
                '',
            ],
        ];
    }
}
