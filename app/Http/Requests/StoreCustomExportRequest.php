<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomExportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'format' => [
                'sometimes',
                'string',
            ],
            'exportable_type' => [
                'required',
                'string',
            ],
            'exportable_id' => [
                'required',
                'int',
            ],
            'field_list' => [
                'required',
                'array',
            ],
            'duration' => [
                'sometimes',
                'int',
            ],
        ];
    }
}
