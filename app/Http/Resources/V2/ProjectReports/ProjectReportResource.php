<?php

namespace App\Http\Resources\V2\ProjectReports;

use App\Http\Resources\V2\Organisation\OrganisationLiteResource;
use App\Http\Resources\V2\Projects\ProjectLiteResource;
use App\Http\Resources\V2\User\UserLiteResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectReportResource extends JsonResource
{
    public function toArray($request)
    {
        $data = [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'framework_key' => $this->framework_key,
            'framework_uuid' => $this->framework_uuid,
            'report_title' => $this->report_title,
            'status' => $this->status,
            'readable_status' => $this->readable_status,
            'update_request_status' => $this->update_request_status,
            'readable_update_request_status' => $this->readable_update_request_status,
            'feedback' => $this->feedback,
            'feedback_fields' => $this->feedback_fields,
            'due_at' => $this->due_at,
            'completion' => $this->completion,
            'readable_completion_status' => $this->readable_completion_status,
            'total_unique_restoration_partners' => $this->total_unique_restoration_partners,
            'direct_restoration_partners' => $this->direct_restoration_partners,
            'indirect_restoration_partners' => $this->indirect_restoration_partners,
            'technical_narrative' => $this->technical_narrative,
            'public_narrative' => $this->public_narrative,
            'landscape_community_contribution' => $this->landscape_community_contribution,
            'top_three_successes' => $this->top_three_successes,
            'challenges_faced' => $this->challenges_faced,
            'lessons_learned' => $this->lessons_learned,
            'maintenance_and_monitoring_activities' => $this->maintenance_and_monitoring_activities,
            'significant_change' => $this->significant_change,
            'pct_survival_to_date' => $this->pct_survival_to_date,
            'survival_calculation' => $this->survival_calculation,
            'survival_comparison' => $this->survival_comparison,
            'ft_smallholder_farmers' => $this->ft_smallholder_farmers,
            'pt_smallholder_farmers' => $this->pt_smallholder_farmers,
            'seasonal_women' => $this->seasonal_women,
            'seasonal_men' => $this->seasonal_men,
            'seasonal_youth' => $this->seasonal_youth,
            'seasonal_smallholder_farmers' => $this->seasonal_smallholder_farmers,
            'seasonal_total' => $this->seasonal_total,
            'volunteer_smallholder_farmers' => $this->volunteer_smallholder_farmers,
            'shared_drive_link' => $this->shared_drive_link,
            'planted_trees' => $this->planted_trees,
            'new_jobs_description' => $this->new_jobs_description,
            'volunteers_work_description' => $this->volunteers_work_description,
            'beneficiaries_description' => $this->beneficiaries_description,
            'beneficiaries_income_increase_description' => $this->beneficiaries_income_increase_description,
            'beneficiaries_skills_knowledge_increase_description' => $this->beneficiaries_skills_knowledge_increase_description,
            'organisation' => new OrganisationLiteResource($this->organisation),
            'project' => new ProjectLiteResource($this->project),
            'site_reports_count' => $this->site_reports_count,
            'nursery_reports_count' => $this->nursery_reports_count,
            'task_uuid' => $this->task_uuid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'submitted_at' => $this->submitted_at,
            'created_by' => $this->handleCreatedBy(),
            'seedlings_grown' => $this->seedlings_grown,
            'trees_planted_count' => $this->trees_planted_count,
            'seeds_planted_count' => $this->seeds_planted_count,
            'regenerated_trees_count' => $this->regenerated_trees_count,
            'community_progress' => $this->community_progress,
            'equitable_opportunities' => $this->equitable_opportunities,
            'local_engagement' => $this->local_engagement,
            'site_addition' => $this->site_addition,
            'paid_other_activity_description' => $this->paid_other_activity_description,
            'other_restoration_partners_description' => $this->other_restoration_partners_description,
            'local_engagement_description' => $this->local_engagement_description,
            'resilience_progress' => $this->resilience_progress,
            'local_governance' => $this->local_governance,
            'adaptive_management' => $this->adaptive_management,
            'scalability_replicability' => $this->scalability_replicability,
            'convergence_jobs_description' => $this->convergence_jobs_description,
            'convergence_schemes' => $this->convergence_schemes,
            'convergence_amount' => $this->convergence_amount,
            'community_partners_assets_description' => $this->community_partners_assets_description,
            'people_knowledge_skills_increased' => $this->people_knowledge_skills_increased,
            'indirect_beneficiaries' => $this->indirect_beneficiaries,
            'indirect_beneficiaries_description' => $this->indirect_beneficiaries_description,
            'workdays_direct_total' => $this->workdays_direct_total,
            'workdays_convergence_total' => $this->workdays_convergence_total,
            'non_tree_total' => $this->non_tree_total,
            'business_milestones' => $this->business_milestones,
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
