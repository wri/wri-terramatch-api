<?php

namespace App\Http\Resources\V2\Sites;

use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use App\Http\Resources\V2\Projects\ProjectLiteResource;
use App\Http\Resources\V2\Stratas\StrataResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'ppc_external_id' => $this->ppc_external_id ?? $this->id,
            'name' => $this->name,
            'framework_key' => $this->framework_key,
            'framework_uuid' => $this->framework_uuid,
            'description' => $this->description,
            'control_site' => $this->control_site,
            'boundary_geojson' => $this->boundary_geojson,
            'history' => $this->history,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'land_tenures' => $this->land_tenures,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'update_request_status' => $this->update_request_status,
            'readable_update_request_status' => $this->readable_update_request_status,
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'survival_rate_planted' => $this->survival_rate_planted,
            'direct_seeding_survival_rate' => $this->direct_seeding_survival_rate,
            'a_nat_regeneration_trees_per_hectare' => $this->a_nat_regeneration_trees_per_hectare,
            'a_nat_regeneration' => $this->a_nat_regeneration > 1 ? intval(round($this->a_nat_regeneration)) : $this->a_nat_regeneration,
            'hectares_to_restore_goal' => $this->hectares_to_restore_goal,
            'total_hectares_restored_sum' => $this->total_hectares_restored_sum,
            'landscape_community_contribution' => $this->landscape_community_contribution,
            'planting_pattern' => $this->planting_pattern,
            'soil_condition' => $this->soil_condition,
            'aim_year_five_crown_cover' => $this->aim_year_five_crown_cover,
            'aim_number_of_mature_trees' => $this->aim_number_of_mature_trees,
            'land_use_types' => $this->land_use_types,
            'restoration_strategy' => $this->restoration_strategy,
            'organisation' => new OrganisationLiteResource($this->organisation),
            'project' => new ProjectLiteResource($this->project),
            'stratas' => StrataResource::collection($this->stratas),
            'site_reports_total' => $this->site_reports_total,
            'overdue_site_reports_total' => $this->overdue_site_reports_total,
            'workday_count' => $this->workday_count,
            // These two are temporary until we have bulk import completed.
            'self_reported_workday_count' => $this->self_reported_workday_count,
            'combined_workday_count' => $this->combined_workday_count,
            'trees_planted_count' => $this->trees_planted_count,
            'regenerated_trees_count' => $this->regenerated_trees_count,
            'approved_regenerated_trees_count' => $this->approved_regenerated_trees_count,
            'migrated' => ! empty($this->old_model),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'has_monitoring_data' => empty($this->has_monitoring_data) ? false : true,
            'seeds_planted_count' => $this->seeds_planted_count,
            'siting_strategy' => $this->siting_strategy,
            'description_siting_strategy' => $this->description_siting_strategy,
            'detailed_intervention_types' => $this->detailed_intervention_types,
        ];

        return $this->appendFilesToResource($data);
    }
}
