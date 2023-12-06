<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePitchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'between:1,255',
            ],
            'description' => [
                'sometimes',
                'required',
                'string',
                'between:1,65535',
            ],
            'land_types' => [
                'sometimes',
                'required',
                'array',
            ],
            'land_types.*' => [
                'distinct',
                'string',
            ],
            'land_ownerships' => [
                'sometimes',
                'required',
                'array',
            ],
            'land_ownerships.*' => [
                'distinct',
                'string',
            ],
            'land_size' => [
                'sometimes',
                'required',
                'string',
            ],
            'land_continent' => [
                'sometimes',
                'required',
                'string',
            ],
            'land_country' => [
                'sometimes',
                'required',
                'string',
            ],
            'land_geojson' => [
                'sometimes',
                'required',
                'string',
                'json',
                'between:1,4294967295',
            ],
            'restoration_methods' => [
                'sometimes',
                'required',
                'array',
                'array_array',
            ],
            'restoration_methods.*' => [
                'distinct',
                'string',
            ],
            'restoration_goals' => [
                'sometimes',
                'required',
                'array',
                'array_array',
            ],
            'restoration_goals.*' => [
                'distinct',
                'string',
            ],
            'funding_sources' => [
                'sometimes',
                'required',
                'array',
                'array_array',
            ],
            'funding_sources.*' => [
                'distinct',
                'string',
            ],
            'funding_amount' => [
                'sometimes',
                'required',
                'integer',
                'between:1,2147483647',
            ],
            'funding_bracket' => [
                'sometimes',
                'required',
                'string',
            ],
            'revenue_drivers' => [
                'sometimes',
                'present',
                'array',
                'array_array',
            ],
            'revenue_drivers.*' => [
                'distinct',
                'string',
            ],
            'estimated_timespan' => [
                'sometimes',
                'required',
                'integer',
                'between:1,2147483647',
            ],
            'long_term_engagement' => [
                'sometimes',
                'present',
                'nullable',
                'boolean',
            ],
            'reporting_frequency' => [
                'sometimes',
                'required',
                'string',
            ],
            'reporting_level' => [
                'sometimes',
                'required',
                'string',
            ],
            'sustainable_development_goals' => [
                'sometimes',
                'present',
                'array',
            ],
            'sustainable_development_goals.*' => [
                'distinct',
                'string',
            ],
            'cover_photo' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'exists:uploads,id',
            ],
            'video' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'exists:uploads,id',
            ],
            'problem' => [
                'sometimes',
                'required',
                'string',
                'between:1,65535',
            ],
            'anticipated_outcome' => [
                'sometimes',
                'required',
                'string',
                'between:1,65535',
            ],
            'who_is_involved' => [
                'sometimes',
                'required',
                'string',
                'between:1,65535',
            ],
            'local_community_involvement' => [
                'sometimes',
                'present',
                'nullable',
                'boolean',
            ],
            'training_involved' => [
                'sometimes',
                'required',
                'boolean',
            ],
            'training_type' => [
                'sometimes',
                'present',
                'nullable',
                'string',
                'between:1,65535',
            ],
            'training_amount_people' => [
                'sometimes',
                'present',
                'nullable',
                'integer',
                'between:0,2147483647',
            ],
            'people_working_in' => [
                'sometimes',
                'required',
                'string',
                'between:1,65535',
            ],
            'people_amount_nearby' => [
                'sometimes',
                'required',
                'integer',
                'between:0,2147483647',
            ],
            'people_amount_abroad' => [
                'sometimes',
                'required',
                'integer',
                'between:0,2147483647',
            ],
            'people_amount_employees' => [
                'sometimes',
                'required',
                'integer',
                'between:0,2147483647',
            ],
            'people_amount_volunteers' => [
                'sometimes',
                'required',
                'integer',
                'between:0,2147483647',
            ],
            'benefited_people' => [
                'sometimes',
                'required',
                'integer',
                'between:0,2147483647',
            ],
            'future_maintenance' => [
                'sometimes',
                'required',
                'string',
                'between:1,65535',
            ],
            'use_of_resources' => [
                'sometimes',
                'required',
                'string',
                'between:1,65535',
            ],
        ];
    }
}
