<?php

namespace App\Http\Requests\V2;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuditStatusRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'entity' => ['sometimes', 'string', 'nullable', 'max:256'],
            'entity_uuid' => ['sometimes', 'string', 'nullable', 'max:256'],
            'status' => ['sometimes', 'string', 'nullable', 'max:256'],
            'comment' => ['sometimes', 'string', 'nullable', 'max:500'],
            'attachment_url' => ['sometimes', 'string', 'nullable', 'max:256'],
        ];
    }
}
