<?php

namespace App\Http\Requests\V2\Forms;

use App\Models\V2\Forms\FormSubmission;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFormSubmissionStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'status' => ['string', 'in:' . implode(',', array_keys(FormSubmission::$statuses))],
            'feedback' => ['sometimes', 'string',  'nullable', 'min:0', 'max:65000'],
            'feedback_fields' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
