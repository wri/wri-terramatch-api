<?php

namespace App\Http\Requests\V2\ProjectPitches;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectPitchRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'organisation_id' => ['required', 'string', 'exists:organisations,uuid'],
            'funding_programme_id' => ['sometimes', 'nullable', 'string', 'exists:funding_programmes,uuid'],
            'project_name' => ['sometimes', 'nullable', 'string', 'between:1,255'],
            'project_objectives' => ['sometimes', 'nullable', 'string', 'between:1,16777215'],
            'project_country' => ['sometimes', 'nullable', 'string'],
            'project_county_district' => ['sometimes', 'nullable','string', 'between:1,255'],
            'how_discovered' => ['sometimes', 'nullable', 'array'],
            'restoration_intervention_types' => ['sometimes', 'nullable', 'array'],
            'capacity_building_needs' => ['sometimes', 'nullable', 'array'],
            'project_budget' => ['sometimes', 'nullable', 'integer', 'between:0,9999999'],
            'total_hectares' => ['sometimes', 'nullable', 'integer', 'between:0,9999999'],
            'total_trees' => ['sometimes', 'nullable','integer', 'between:0,9999999'],
            'tags' => ['sometimes', 'nullable', 'array'],
            'expected_active_restoration_start_date' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'expected_active_restoration_end_date' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'description_of_project_timeline' => ['sometimes', 'string', 'min:1', 'max:65335'],
            'proj_partner_info' => ['sometimes', 'string', 'min:1', 'max:65335'],
            'land_tenure_proj_area' => ['sometimes', 'array'],
            'landholder_comm_engage' => ['sometimes', 'string', 'min:1', 'max:65335'],
            'proj_success_risks' => ['sometimes', 'nullable', 'string', 'min:1', 'max:65335'],
            'monitor_eval_plan' => ['sometimes', 'nullable', 'string', 'min:1', 'max:65335'],
            'proj_boundary' => ['sometimes', 'nullable', 'string', 'min:1', 'max:65335'],
            'sustainable_dev_goals' => ['sometimes', 'nullable', 'array'],
            'proj_area_description' => ['sometimes', 'nullable', 'string', 'min:1', 'max:65335'],
            'proposed_num_sites' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:9999999'],
            'environmental_goals' => ['sometimes', 'nullable', 'string', 'min:1', 'max:65335'],
            'proposed_num_nurseries' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:9999999'],
            'curr_land_degradation' => ['sometimes', 'nullable', 'string', 'min:1', 'max:65335'],
            'proj_impact_socieconom' => ['sometimes', 'nullable', 'string', 'min:1', 'max:65335'],
            'proj_impact_foodsec' => ['sometimes', 'nullable', 'string', 'min:1', 'max:65335'],
            'proj_impact_watersec' => ['sometimes', 'nullable', 'string', 'min:1', 'max:65335'],
            'proj_impact_jobtypes' => ['sometimes', 'nullable', 'string', 'min:1', 'max:65335'],
            'num_jobs_created' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:9999999'],
            'pct_employees_men' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'pct_employees_women' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'pct_employees_18to35' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'pct_employees_older35' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'proj_beneficiaries' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:9999999'],
            'pct_beneficiaries_women' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'pct_beneficiaries_small' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'pct_beneficiaries_large' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'pct_beneficiaries_youth' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'pct_beneficiaries_scheduled_classes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'pct_beneficiaries_scheduled_tribes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'pct_beneficiaries_backward_class' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'main_causes_of_degradation' => ['sometimes', 'nullable', 'string', 'min:0', 'max:65335'],
            'seedlings_source' => ['sometimes', 'nullable', 'string'],
            'monitoring_evaluation_plan' => ['sometimes', 'nullable', 'string'],
            'states' => ['sometimes', 'nullable', 'array'],
            'hectares_first_yr' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:9999999'],
            'total_trees_first_yr' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:9999999'],
            'main_degradation_causes' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
