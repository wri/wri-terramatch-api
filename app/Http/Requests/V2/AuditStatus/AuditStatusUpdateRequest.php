<?php

namespace App\Http\Requests\V2\AuditStatus;

use Illuminate\Foundation\Http\FormRequest;

class AuditStatusUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'sometimes|nullable|string',
            'comment' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string',
            'is_active' => 'sometimes|nullable|boolean',
            'request_removed' => 'sometimes|nullable|boolean',
        ];
    }
}
