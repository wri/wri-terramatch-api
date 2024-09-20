<?php

namespace App\Http\Requests\V2\Reminder;

use Illuminate\Foundation\Http\FormRequest;

class SendReminderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'feedback' => ['sometimes', 'string', 'nullable'],
        ];
    }
}
