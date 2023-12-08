<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class FilterTerrafundProgrammeSubmissionByDateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'start_date' => [
                'required',
                'string',
                'date_format:Y-m-d',
                'before_or_equal:now',
            ],
            'end_date' => [
                'required',
                'string',
                'date_format:Y-m-d',
                'after:start_date',
                'before_or_equal:now',
            ],
        ];
    }
}
