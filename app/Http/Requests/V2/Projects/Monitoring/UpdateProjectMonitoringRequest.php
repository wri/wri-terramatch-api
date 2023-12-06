<?php

namespace App\Http\Requests\V2\Projects\Monitoring;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectMonitoringRequest extends FormRequest
{
    public function rules()
    {
        return [
            'status' => 'required|string',

            'total_hectares' => 'sometimes|nullable|numeric',
            'ha_mangrove' => 'sometimes|nullable|numeric',
            'ha_assisted' => 'sometimes|nullable|numeric',
            'ha_agroforestry' => 'sometimes|nullable|numeric',
            'ha_reforestation' => 'sometimes|nullable|numeric',
            'ha_peatland' => 'sometimes|nullable|numeric',
            'ha_riparian' => 'sometimes|nullable|numeric',
            'ha_enrichment' => 'sometimes|nullable|numeric',
            'ha_nucleation' => 'sometimes|nullable|numeric',
            'ha_silvopasture' => 'sometimes|nullable|numeric',
            'ha_direct' => 'sometimes|nullable|numeric',

            'tree_count' => 'sometimes|nullable|numeric',
            'tree_cover' => 'sometimes|nullable|numeric',
            'field_tree_count' => 'sometimes|nullable|numeric',
            'tree_cover_loss' => 'sometimes|nullable|numeric',
            'carbon_benefits' => 'sometimes|nullable|numeric',
            'number_of_esrp' => 'sometimes|nullable|numeric',

            'field_tree_regenerated' => 'sometimes|nullable|numeric',
            'field_tree_survival_percent' => 'sometimes|nullable|numeric',

            'start_date' => 'required|date_format:Y-m-d|before:end_date',
            'end_date' => 'required|date_format:Y-m-d|after:start_date',
        ];
    }
}
