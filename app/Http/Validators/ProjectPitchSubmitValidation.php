<?php

namespace App\Http\Validators;

class ProjectPitchSubmitValidation
{
    public function rules(): array
    {
        return [
            'funding_programme_id' => ['sometimes', 'nullable', 'string', 'exists:funding_programmes,uuid'],
            'project_name' => ['required', 'string', 'between:1,255'],
            'project_objectives' => ['sometimes', 'nullable', 'string', 'between:1,16777215'],
            'project_country' => ['sometimes', 'nullable', 'string'],
            'project_county_district' => ['sometimes', 'nullable', 'string', 'between:1,255'],
            'restoration_intervention_types' => ['sometimes', 'nullable', 'array'],
            'capacity_building_needs' => ['sometimes', 'nullable', 'array'],
            'total_hectares' => ['sometimes', 'nullable', 'integer', 'between:0,4294967295'],
            'total_trees' => ['sometimes', 'nullable', 'integer', 'between:0,4294967295'],
            'tags' => ['sometimes', 'nullable', 'array'],
            'how_discovered' => ['sometimes', 'nullable', 'array'],
            'project_budget' => ['sometimes', 'nullable', 'integer', 'between:0,4294967295'],
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
