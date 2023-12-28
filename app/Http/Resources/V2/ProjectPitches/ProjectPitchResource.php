<?php

namespace App\Http\Resources\V2\ProjectPitches;

use App\Http\Resources\V2\Forms\FormSubmissionLiteResource;
use App\Http\Resources\V2\FundingProgrammes\LimitedFundingProgrammeResource;
use App\Http\Resources\V2\TreeSpecies\TreeSpeciesResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectPitchResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'organisation_id' => $this->organisation_id,
            'funding_programme' => new LimitedFundingProgrammeResource($this->fundingProgramme),
            'project_name' => $this->project_name,
            'project_objectives' => $this->project_objectives,
            'project_budget' => $this->project_budget,
            'project_country' => $this->project_country,
            'project_county_district' => $this->project_county_district,
            'restoration_intervention_types' => $this->restoration_intervention_types,
            'total_hectares' => $this->total_hectares,
            'total_trees' => $this->total_trees,
            'capacity_building_needs' => $this->capacity_building_needs,
            'how_discovered' => $this->how_discovered,
            'expected_active_restoration_start_date' => $this->expected_active_restoration_start_date,
            'expected_active_restoration_end_date' => $this->expected_active_restoration_end_date,
            'description_of_project_timeline' => $this->description_of_project_timeline,
            'proj_partner_info' => $this->proj_partner_info,
            'land_tenure_proj_area' => $this->land_tenure_proj_area,
            'landholder_comm_engage' => $this->landholder_comm_engage,
            'proj_success_risks' => $this->proj_success_risks,
            'monitor_eval_plan' => $this->monitor_eval_plan,
            'proj_boundary' => $this->proj_boundary,
            'sustainable_dev_goals' => $this->sustainable_dev_goals,
            'proj_area_description' => $this->proj_area_description,
            'proposed_num_sites' => $this->proposed_num_sites,
            'environmental_goals' => $this->environmental_goals,
            'proposed_num_nurseries' => $this->proposed_num_nurseries,
            'curr_land_degradation' => $this->curr_land_degradation,
            'proj_impact_socieconom' => $this->proj_impact_socieconom,
            'proj_impact_foodsec' => $this->proj_impact_foodsec,
            'proj_impact_watersec' => $this->proj_impact_watersec,
            'proj_impact_jobtypes' => $this->proj_impact_jobtypes,
            'num_jobs_created' => $this->num_jobs_created,
            'pct_employees_men' => $this->pct_employees_men,
            'pct_employees_women' => $this->pct_employees_women,
            'pct_employees_18to35' => $this->pct_employees_18to35,
            'pct_employees_older35' => $this->pct_employees_older35,
            'proj_beneficiaries' => $this->proj_beneficiaries,
            'pct_beneficiaries_women' => $this->pct_beneficiaries_women,
            'pct_beneficiaries_small' => $this->pct_beneficiaries_small,
            'pct_beneficiaries_large' => $this->pct_beneficiaries_large,
            'pct_beneficiaries_youth' => $this->pct_beneficiaries_youth,
            'main_causes_of_degradation' => $this->main_causes_of_degradation,
            'main_degradation_causes' => $this->main_degradation_causes,
            'seedlings_source' => $this->seedlings_source,
            'monitoring_evaluation_plan' => $this->monitoring_evaluation_plan,
            'states' => $this->states,
            'hectares_first_yr' => $this->hectares_first_yr,
            'total_trees_first_yr' => $this->total_trees_first_yr,
            'pct_beneficiaries_backward_class' => $this->pct_beneficiaries_backward_class,
            'land_systems' => $this->land_systems,
            'tree_restoration_practices' => $this->tree_restoration_practices,
            'tree_species' => TreeSpeciesResource::collection($this->treeSpecies),
            'form_submissions' => FormSubmissionLiteResource::collection($this->formSubmissions),
            'tags' => $this->buildTagList(),
            'siting_strategy_description' => $this->siting_strategy_description,
            'siting_strategy' => $this->siting_strategy,

            'theory_of_change' => $this->theory_of_change,
            'proposed_gov_partners' => $this->proposed_gov_partners,
            'pct_sch_tribe' => $this->pct_sch_tribe,
            'sustainability_plan' => $this->sustainability_plan,
            'replication_plan' => $this->replication_plan,
            'replication_challenges' => $this->replication_challenges,
            'solution_market_size' => $this->solution_market_size,
            'affordability_of_solution' => $this->affordability_of_solution,
            'growth_trends_business' => $this->growth_trends_business,
            'limitations_on_scope' => $this->limitations_on_scope,
            'business_model_replication_plan' => $this->business_model_replication_plan,
            'biodiversity_impact' => $this->biodiversity_impact,
            'water_source' => $this->water_source,
            'climate_resilience' => $this->climate_resilience,
            'soil_health' => $this->soil_health,

            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];

        return $this->appendFilesToResource($data);
    }

    private function buildTagList(): array
    {
        $list = [];
        foreach ($this->tags as $tag) {
            $list[$tag->slug] = $tag->name ;
        }

        return $list;
    }
}
