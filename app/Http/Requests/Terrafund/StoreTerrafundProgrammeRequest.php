<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrafundProgrammeRequest extends FormRequest
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
                'between:1,65000',
            ],
            'planting_start_date' => [
                'required',
                'date_format:Y-m-d',
                'before:planting_end_date',
            ],
            'planting_end_date' => [
                'required',
                'date_format:Y-m-d',
                'after:planting_start_date',
            ],
            'budget' => [
                'required',
                'numeric',
                'min:0',
                'max:2147483647',
            ],
            'status' => ['required', 'string', 'in:' . implode(',', array_unique(array_values(config('data.terrafund.programme.status'))))],
            'project_country' => ['required', 'string', 'in:' . implode(',', array_unique(array_values(config('data.countries'))))],
            'home_country' => ['nullable', 'string', 'in:' . implode(',', array_unique(array_values(config('data.countries'))))],
            'boundary_geojson' => [
                'required',
                'string',
                'json',
                'between:1,4294967295',
            ],
            'history' => [
                'required',
                'string',
                'max:65000',
            ],
            'objectives' => [
                'required',
                'string',
                'between:1,65000',
            ],
            'environmental_goals' => [
                'required',
                'string',
                'between:1,65000',
            ],
            'socioeconomic_goals' => [
                'required',
                'string',
                'between:1,65000',
            ],
            'sdgs_impacted' => [
                'required',
                'string',
                'between:1,65000',
            ],
            'long_term_growth' => [
                'required',
                'string',
                'between:1,65000',
            ],
            'community_incentives' => [
                'required',
                'string',
                'between:1,65000',
            ],
            'total_hectares_restored' => [
                'required',
                'numeric',
                'min:0',
                'max:2147483647',
            ],
            'trees_planted' => [
                'required',
                'numeric',
                'min:0',
                'max:2147483647',
            ],
            'jobs_created' => [
                'required',
                'numeric',
                'min:0',
                'max:2147483647',
            ],
        ];
    }
}
