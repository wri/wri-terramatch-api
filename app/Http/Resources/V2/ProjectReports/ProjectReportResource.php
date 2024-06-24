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
            'workdays_paid' => $this->workdays_paid,
            'workdays_volunteer' => $this->workdays_volunteer,
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
            'ft_women' => $this->ft_women,
            'ft_men' => $this->ft_men,
            'ft_youth' => $this->ft_youth,
            'ft_smallholder_farmers' => $this->ft_smallholder_farmers,
            'ft_total' => $this->ft_total,
            'pt_non_youth' => $this->pt_non_youth,
            'pt_women' => $this->pt_women,
            'pt_men' => $this->pt_men,
            'pt_youth' => $this->pt_youth,
            'pt_smallholder_farmers' => $this->pt_smallholder_farmers,
            'pt_total' => $this->pt_total,
            'workdays_total' => $this->workdays_total,
            'seasonal_women' => $this->seasonal_women,
            'seasonal_men' => $this->seasonal_men,
            'seasonal_youth' => $this->seasonal_youth,
            'seasonal_smallholder_farmers' => $this->seasonal_smallholder_farmers,
            'seasonal_total' => $this->seasonal_total,
            'volunteer_women' => $this->volunteer_women,
            'volunteer_men' => $this->volunteer_men,
            'volunteer_youth' => $this->volunteer_youth,
            'volunteer_smallholder_farmers' => $this->volunteer_smallholder_farmers,
            'volunteer_total' => $this->volunteer_total,
            'shared_drive_link' => $this->shared_drive_link,
            'planted_trees' => $this->planted_trees,
            'new_jobs_description' => $this->new_jobs_description,
            'volunteers_work_description' => $this->volunteers_work_description,
            'ft_jobs_non_youth' => $this->ft_jobs_non_youth,
            'ft_jobs_youth' => $this->ft_jobs_youth,
            'volunteer_non_youth' => $this->volunteer_non_youth,
            'beneficiaries' => $this->beneficiaries,
            'beneficiaries_description' => $this->beneficiaries_description,
            'beneficiaries_women' => $this->beneficiaries_women,
            'beneficiaries_men' => $this->beneficiaries_men,
            'beneficiaries_non_youth' => $this->beneficiaries_non_youth,
            'beneficiaries_youth' => $this->beneficiaries_youth,
            'beneficiaries_smallholder' => $this->beneficiaries_smallholder,
            'beneficiaries_large_scale' => $this->beneficiaries_large_scale,
            'beneficiaries_income_increase' => $this->beneficiaries_income_increase,
            'beneficiaries_income_increase_description' => $this->beneficiaries_income_increase_description,
            'beneficiaries_skills_knowledge_increase' => $this->beneficiaries_skills_knowledge_increase,
            'beneficiaries_skills_knowledge_increase_description' => $this->beneficiaries_skills_knowledge_increase_description,
            'organisation' => new OrganisationLiteResource($this->organisation),
            'project' => new ProjectLiteResource($this->project),
            'site_reports_count' => $this->site_reports_count,
            'nursery_reports_count' => $this->nursery_reports_count,
            'total_jobs_created' => $this->total_jobs_created,
            'task_uuid' => $this->task_uuid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'submitted_at' => $this->submitted_at,
            'migrated' => ! empty($this->old_model),
            'created_by' => $this->handleCreatedBy(),
            'seedlings_grown' => $this->seedlings_grown,
            'trees_planted_count' => $this->trees_planted_count,
            'seeds_planted_count' => $this->seeds_planted_count,
            'community_progress' => $this->community_progress,
            'equitable_opportunities' => $this->equitable_opportunities,
            'local_engagement' => $this->local_engagement,
            'site_addition' => $this->site_addition,
            'paid_other_activity_description' => $this->paid_other_activity_description,
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
