<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTerrafundProgrammeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'name' => [
                'string',
                'between:1,255',
            ],
            'description' => [
                'string',
                'between:1,65000',
            ],
            'planting_start_date' => [
                'date_format:Y-m-d',
                'before:planting_end_date',
            ],
            'planting_end_date' => [
                'date_format:Y-m-d',
                'after:planting_start_date',
            ],
            'budget' => [
                'numeric',
                'min:0',
                'max:2147483647',
            ],
            'status' => ['string', 'in:' . implode(',', array_unique(array_values(config('data.terrafund.programme.status'))))],
            'project_country' => ['string', 'in:' . implode(',', array_unique(array_values(config('data.countries'))))],
            'home_country' => ['string', 'in:' . implode(',', array_unique(array_values(config('data.countries'))))],
            'boundary_geojson' => [
                'string',
                'json',
                'between:1,4294967295',
            ],
            'history' => [
                'string',
                'max:65000',
            ],
            'objectives' => [
                'string',
                'between:1,65000',
            ],
            'environmental_goals' => [
                'string',
                'between:1,65000',
            ],
            'socioeconomic_goals' => [
                'string',
                'between:1,65000',
            ],
            'sdgs_impacted' => [
                'string',
                'between:1,65000',
            ],
            'long_term_growth' => [
                'string',
                'between:1,65000',
            ],
            'community_incentives' => [
                'string',
                'between:1,65000',
            ],
            'total_hectares_restored' => [
                'numeric',
                'min:0',
                'max:2147483647',
            ],
            'trees_planted' => [
                'numeric',
                'min:0',
                'max:2147483647',
            ],
            'jobs_created' => [
                'numeric',
                'min:0',
                'max:2147483647',
            ],
        ];
    }
}
