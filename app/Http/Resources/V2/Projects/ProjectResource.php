<?php

namespace App\Http\Resources\V2\Projects;

use App\Http\Resources\V2\Applications\ApplicationLiteResource;
use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'ppc_external_id' => $this->ppc_external_id ?? $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'project_status' => $this->project_status,
            'update_request_status' => $this->update_request_status,
            'readable_update_request_status' => $this->readable_update_request_status,
            'framework_key' => $this->framework_key,
            'framework_uuid' => $this->framework_uuid,
            'organisation_id' => $this->organisation_id,
            'boundary_geojson' => $this->boundary_geojson,
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'country' => $this->country,
            'continent' => $this->continent,
            'planting_start_date' => $this->planting_start_date,
            'planting_end_date' => $this->planting_end_date,
            'description' => $this->description,
            'budget' => $this->budget,
            'history' => $this->history,
            'objectives' => $this->objectives,
            'environmental_goals' => $this->environmental_goals,
            'socioeconomic_goals' => $this->socioeconomic_goals,
            'sdgs_impacted' => $this->sdgs_impacted,
            'long_term_growth' => $this->long_term_growth,
            'community_incentives' => $this->community_incentives,
            'jobs_created_goal' => $this->jobs_created_goal,
            'total_hectares_restored_goal' => $this->total_hectares_restored_goal,
            'total_hectares_restored_count' => $this->total_hectares_restored_count,
            'total_hectares_restored_sum' => $this->total_hectares_restored_sum,
            'trees_grown_goal' => $this->trees_grown_goal,
            'survival_rate' => $this->survival_rate,
            'year_five_crown_cover' => $this->year_five_crown_cover,
            'monitored_tree_cover' => $this->monitored_tree_cover,
            'land_use_types' => $this->land_use_types,
            'restoration_strategy' => $this->restoration_strategy,
            'trees_restored_count' => $this->trees_restored_count,
            'trees_planted_count' => $this->trees_planted_count,
            'approved_trees_planted_count' => $this->approved_trees_planted_count,
            'seeds_planted_count' => $this->seeds_planted_count,
            'regenerated_trees_count' => $this->regenerated_trees_count,
            'workday_count' => $this->workday_count,
            // These two are temporary until we have bulk import completed.
            'self_reported_workday_count' => $this->self_reported_workday_count,
            'combined_workday_count' => $this->combined_workday_count,
            'total_jobs_created' => $this->total_jobs_created,
            'total_approved_jobs_created' => $this->total_approved_jobs_created,
            'approved_volunteers_count' => $this->approved_volunteers_count,
            'total_sites' => $this->total_sites,
            'total_nurseries' => $this->total_nurseries,
            'total_project_reports' => $this->total_project_reports,
            'total_overdue_reports' => $this->total_overdue_reports,
            'has_monitoring_data' => empty($this->has_monitoring_data) ? false : true,
            'organization_name' => $this->organization_name,
            'project_county_district' => $this->project_county_district,
            'description_of_project_timeline' => $this->description_of_project_timeline,
            'siting_strategy_description' => $this->siting_strategy_description,
            'siting_strategy' => $this->siting_strategy,
            'landholder_comm_engage' => $this->landholder_comm_engage,
            'proj_partner_info' => $this->proj_partner_info,
            'proj_success_risks' => $this->proj_success_risks,
            'monitor_eval_plan' => $this->monitor_eval_plan,
            'seedlings_source' => $this->seedlings_source,
            'pct_employees_men' => $this->pct_employees_men,
            'pct_employees_women' => $this->pct_employees_women,
            'pct_employees_18to35' => $this->pct_employees_18to35,
            'pct_employees_older35' => $this->pct_employees_older35,
            'proj_beneficiaries' => $this->proj_beneficiaries,
            'pct_beneficiaries_women' => $this->pct_beneficiaries_women,
            'pct_beneficiaries_small' => $this->pct_beneficiaries_small,
            'pct_beneficiaries_large' => $this->pct_beneficiaries_large,
            'pct_beneficiaries_youth' => $this->pct_beneficiaries_youth,
            'land_tenure_project_area' => $this->land_tenure_project_area,
            'proj_impact_biodiv' => $this->proj_impact_biodiv,
            'proj_impact_foodsec' => $this->proj_impact_foodsec,
            'proposed_gov_partners' => $this->proposed_gov_partners,
            'states' => $this->states,
            'organisation' => new OrganisationLiteResource($this->organisation),
            'application' => new ApplicationLiteResource($this->application),
            'migrated' => ! empty($this->old_model),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'trees_restored_ppc' =>
                $this->getTreesGrowingThroughAnr($this->sites) + (($this->trees_planted_count + $this->seeds_planted_count) * ($this->survival_rate / 100)),
        ];

        return $this->appendFilesToResource($data);
    }

    public function getTreesGrowingThroughAnr($sites)
    {
        return $sites->sum(function ($site) {
            return $site->reports->sum('num_trees_regenerating');
        });
    }
}
