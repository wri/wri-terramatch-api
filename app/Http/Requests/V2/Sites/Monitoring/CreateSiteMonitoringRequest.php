<?php

namespace App\Http\Requests\V2\Sites\Monitoring;

use Illuminate\Foundation\Http\FormRequest;

class CreateSiteMonitoringRequest extends FormRequest
{
    public function rules()
    {
        return [
            'site_uuid' => 'required|exists:v2_sites,uuid',
            'tree_count' => 'sometimes|nullable|numeric',
            'tree_cover' => 'sometimes|nullable|numeric',
            'field_tree_count' => 'sometimes|nullable|numeric',
            'measurement_date' => 'sometimes|date_format:Y-m-d',
        ];
    }
}
