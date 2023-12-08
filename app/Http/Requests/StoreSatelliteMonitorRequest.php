<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSatelliteMonitorRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'satellite_monitorable_type' => [
                'required',
                'in:App\Models\Programme,programme,App\Models\Site,site,App\Models\Terrafund\TerrafundProgramme,terrafund_programme',
            ],
            'satellite_monitorable_id' => [
                'required',
                'integer',
            ],
            'map' => [
                'required',
                'integer',
                'exists:uploads,id',
            ],
            'alt_text' => [
                'nullable',
                'string',
                'between:1,255',
            ],
        ];
    }
}
