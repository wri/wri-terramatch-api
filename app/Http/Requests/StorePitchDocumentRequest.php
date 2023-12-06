<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePitchDocumentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'pitch_id' => [
                'required',
                'integer',
                'exists:pitches,id',
            ],
            'name' => [
                'required',
                'string',
                'between:1,255',
            ],
            'type' => [
                'required',
                'string',
            ],
            'document' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
        ];
    }
}
