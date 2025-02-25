<?php

namespace App\Http\Resources\V2\SiteReports;

use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use App\Http\Resources\V2\Projects\ProjectLiteResource;
use App\Http\Resources\V2\Sites\SiteLiteResource;
use App\Http\Resources\V2\User\UserLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteReportResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'task_uuid' => $this->task_uuid,
            'framework_key' => $this->framework_key,
            'framework_uuid' => $this->framework_uuid,
            'report_title' => $this->report_title,
            'project_report_title' => data_get($this->project, 'report_title'),

            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'readable_completion_status' => $this->readable_completion_status,
            'update_request_status' => $this->update_request_status,
            'readable_update_request_status' => $this->readable_update_request_status,
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'nothing_to_report' => $this->nothing_to_report,

            'title' => $this->title,
            'seeds_planted' => $this->seeds_planted,
            'technical_narrative' => $this->technical_narrative,
            'public_narrative' => $this->public_narrative,
            'disturbance_details' => $this->disturbance_details,
            'shared_drive_link' => $this->shared_drive_link,
            'polygon_status' => $this->polygon_status,
            'paid_other_activity_description' => $this->paid_other_activity_description,

            'total_trees_planted_count' => $this->total_trees_planted_count,
            'total_seeds_planted_count' => $this->total_seeds_planted_count,

            'invasive_species_removed' => $this->invasive_species_removed,
            'invasive_species_management' => $this->invasive_species_management,
            'soil_water_restoration_description' => $this->soil_water_restoration_description,
            'water_structures' => $this->water_structures,
            'site_community_partners_description' => $this->site_community_partners_description,
            'site_community_partners_income_increase_description' => $this->site_community_partners_income_increase_description,

            'due_at' => $this->due_at,
            'approved_at' => $this->approved_at,
            'submitted_at' => $this->submitted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'organisation' => new OrganisationLiteResource($this->organisation),
            'project' => new ProjectLiteResource($this->project),
            'site' => new SiteLiteResource($this->site),
            'migrated' => ! empty($this->old_model),
            'approved_by' => new UserLiteResource($this->approvedBy),
            'created_by' => $this->handleCreatedBy(),
            'num_trees_regenerating' => $this->num_trees_regenerating,
            'regeneration_description' => $this->regeneration_description,
            'total_non_tree_species_planted_count' => $this->total_non_tree_species_planted_count,
            'total_tree_replanting_count' => $this->total_tree_replanting_count,

            'pct_survival_to_date' => $this->pct_survival_to_date,
            'survival_calculation' => $this->survival_calculation,
            'survival_description' => $this->survival_description,
            'maintenance_activities' => $this->maintenance_activities,
        ];

        return $this->appendFilesToResource($data);
    }

    public function handleCreatedBy()
    {
        if (empty($this->created_by) && ! empty($this->old_model)) {
            $class = app($this->old_model);
            $model = $class::find($this->old_id);
            if (! empty($model)) {
                return data_get($model, 'created_by');
            }
        }

        return new UserLiteResource($this->createdBy);
    }
}
