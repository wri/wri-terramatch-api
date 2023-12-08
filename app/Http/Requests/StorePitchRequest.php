<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePitchRequest extends FormRequest
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
            'description' => [
                'required',
                'string',
                'between:1,65535',
            ],
            'land_types' => [
                'required',
                'array',
            ],
            'land_types.*' => [
                'distinct',
                'string',
            ],
            'land_ownerships' => [
                'required',
                'array',
            ],
            'land_ownerships.*' => [
                'distinct',
                'string',
            ],
            'land_size' => [
                'required',
                'string',
            ],
            'land_continent' => [
                'required',
                'string',
            ],
            'land_country' => [
                'required',
                'string',
            ],
            'land_geojson' => [
                'required',
                'string',
                'json',
                'between:1,4294967295',
            ],
            'restoration_methods' => [
                'required',
                'array',
            ],
            'restoration_methods.*' => [
                'distinct',
                'string',
            ],
            'restoration_goals' => [
                'required',
                'array',
            ],
            'restoration_goals.*' => [
                'distinct',
                'string',
            ],
            'funding_sources' => [
                'required',
                'array',
            ],
            'funding_sources.*' => [
                'distinct',
                'string',
            ],
            'funding_amount' => [
                'required',
                'integer',
                'between:1,2147483647',
            ],
            'funding_bracket' => [
                'required',
                'string',
            ],
            'revenue_drivers' => [
                'present',
                'array',
            ],
            'revenue_drivers.*' => [
                'distinct',
                'string',
            ],
            'estimated_timespan' => [
                'required',
                'integer',
                'between:1,2147483647',
            ],
            'long_term_engagement' => [
                'present',
                'nullable',
                'boolean',
            ],
            'reporting_frequency' => [
                'required',
                'string',
            ],
            'reporting_level' => [
                'required',
                'string',
            ],
            'sustainable_development_goals' => [
                'present',
                'array',
            ],
            'sustainable_development_goals.*' => [
                'distinct',
                'string',
            ],
            'cover_photo' => [
                'present',
                'nullable',
                'integer',
                'exists:uploads,id',
            ],
            'video' => [
                'present',
                'nullable',
                'integer',
                'exists:uploads,id',
            ],
            'problem' => [
                'required',
                'string',
                'between:1,65535',
            ],
            'anticipated_outcome' => [
                'required',
                'string',
                'between:1,65535',
            ],
            'who_is_involved' => [
                'required',
                'string',
                'between:1,65535',
            ],
            'local_community_involvement' => [
                'present',
                'nullable',
                'boolean',
            ],
            'training_involved' => [
                'required',
                'boolean',
            ],
            'training_type' => [
                'present',
                'nullable',
                'string',
                'between:1,65535',
            ],
            'training_amount_people' => [
                'present',
                'nullable',
                'integer',
                'between:0,2147483647',
            ],
            'people_working_in' => [
                'required',
                'string',
                'between:1,65535',
            ],
            'people_amount_nearby' => [
                'required',
                'integer',
                'between:0,2147483647',
            ],
            'people_amount_abroad' => [
                'required',
                'integer',
                'between:0,2147483647',
            ],
            'people_amount_employees' => [
                'required',
                'integer',
                'between:0,2147483647',
            ],
            'people_amount_volunteers' => [
                'required',
                'integer',
                'between:0,2147483647',
            ],
            'benefited_people' => [
                'required',
                'integer',
                'between:0,2147483647',
            ],
            'future_maintenance' => [
                'required',
                'string',
                'between:1,65535',
            ],
            'use_of_resources' => [
                'required',
                'string',
                'between:1,65535',
            ],
        ];
    }
}
