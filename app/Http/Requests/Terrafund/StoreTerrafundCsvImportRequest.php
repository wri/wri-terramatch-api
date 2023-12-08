<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrafundCsvImportRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'upload_id' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
            'treeable_type' => [
                'required',
                'string',
                'in:programme,nursery',
            ],
            'treeable_id' => [
                'required',
                'integer',
            ],
        ];
    }
}
