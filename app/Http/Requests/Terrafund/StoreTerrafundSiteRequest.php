<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrafundSiteRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'between:1,255',
            ],
            'start_date' => [
                'required',
                'date_format:Y-m-d',
                'before:end_date',
            ],
            'end_date' => [
                'required',
                'date_format:Y-m-d',
                'after:start_date',
            ],
            'boundary_geojson' => [
                'required',
                'string',
                'json',
                'between:1,4294967295',
            ],
            'restoration_methods' => [
                'required',
                'array',
            ],
            'restoration_methods.*' => ['distinct', 'string', 'in:' . implode(',', array_unique(array_values(config('data.terrafund.site.restoration_methods'))))],
            'land_tenures' => [
                'required',
                'array',
            ],
            'land_tenures.*' => ['distinct', 'string', 'in:' . implode(',', array_unique(array_values(config('data.terrafund.site.land_tenures'))))],
            'hectares_to_restore' => [
                'required',
                'numeric',
                'min:0',
                'max:2147483647',
            ],
            'landscape_community_contribution' => [
                'required',
                'string',
                'between:1,65000',
            ],
            'disturbances' => [
                'required',
                'string',
                'between:1,65000',
            ],
        ];
    }
}
