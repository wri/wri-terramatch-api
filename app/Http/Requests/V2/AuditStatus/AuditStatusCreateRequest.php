<?php

namespace App\Http\Requests\V2\AuditStatus;

use Illuminate\Foundation\Http\FormRequest;

class AuditStatusCreateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'auditable_type' => 'required|string|in:Site,Project,SitePolygon',
            'auditable_uuid' => 'required|string',
            'type' => 'sometimes|nullable|string',
            'comment' => 'sometimes|nullable|string',
            'status' => 'sometimes|nullable|string',
        ];
    }
}
