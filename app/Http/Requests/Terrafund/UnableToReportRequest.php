<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class UnableToReportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'reason' => [
                'required',
                'string',
                'between:1,65000',
            ],
        ];
    }
}
