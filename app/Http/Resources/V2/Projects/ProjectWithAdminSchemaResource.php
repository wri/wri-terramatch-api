<?php

namespace App\Http\Resources\V2\Projects;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectWithAdminSchemaResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'project_status' => $this->project_status,
            'framework_key' => $this->framework_key,
            'organisation_id' => $this->organisation_id,
            'boundary_geojson' => $this->boundary_geojson,
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
            'income_generating_activities' => $this->income_generating_activities,
            'sdgs_impacted' => $this->sdgs_impacted,
            'long_term_growth' => $this->long_term_growth,
            'community_incentives' => $this->community_incentives,
            'jobs_created_goal' => $this->jobs_created_goal,
            'total_hectares_restored_goal' => $this->total_hectares_restored_goal,
            'trees_grown_goal' => $this->trees_grown_goal,
            'survival_rate' => $this->survival_rate,
            'year_five_crown_cover' => $this->year_five_crown_cover,
            'monitored_tree_cover' => $this->monitored_tree_cover,
            'land_use_types' => $this->land_use_types,
            'restoration_strategy' => $this->restoration_strategy,
            'level_1_project' => $this->level_1_project,
            'level_2_project' => $this->level_2_project,
            'land_tenure_approach' => $this->land_tenure_approach,
            'seedlings_procurement' => $this->seedlings_procurement,
            'jobs_goal_description' => $this->jobs_goal_description,
            'volunteers_goal_description' => $this->volunteers_goal_description,
            'community_engagement_plan' => $this->community_engagement_plan,
            'direct_beneficiaries_goal_description' => $this->direct_beneficiaries_goal_description,
            'elp_project' => $this->elp_project,
            'consortium' => $this->consortium,
            'landowner_agreement' => $this->landowner_agreement,

            'trees_restored_count' => $this->trees_restored_count,
            'trees_planted_count' => $this->trees_planted_count,
            'seeds_planted_count' => $this->seeds_planted_count,
            'regenerated_trees_count' => $this->regenerated_trees_count,
            'approved_regenerated_trees_count' => $this->approved_regenerated_trees_count,
            'workday_count' => $this->workday_count,
            'total_jobs_created' => $this->total_approved_jobs_created,
            'total_sites' => $this->total_sites,
            'total_nurseries' => $this->total_nurseries,
            'total_project_reports' => $this->total_project_reports,
            'total_overdue_reports' => $this->total_overdue_reports,
            'has_monitoring_data' => $this->has_monitoring_data,
        ];

        return $this->appendFilesToResource($data);
    }
}
