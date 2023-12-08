<?php

namespace App\Resources\Terrafund;

use App\Models\Terrafund\TerrafundProgrammeSubmission as TerrafundProgrammeSubmissionModel;
use App\Resources\Resource;
use Illuminate\Support\Str;

class TerrafundProgrammeSubmissionResource extends Resource
{
    public function __construct(TerrafundProgrammeSubmissionModel $terrafundProgrammeSubmission)
    {
        $this->id = $terrafundProgrammeSubmission->id;
        $this->landscape_community_contribution = $terrafundProgrammeSubmission->landscape_community_contribution;
        $this->top_three_successes = $terrafundProgrammeSubmission->top_three_successes;
        $this->challenges_and_lessons = $terrafundProgrammeSubmission->challenges_and_lessons;
        $this->maintenance_and_monitoring_activities = $terrafundProgrammeSubmission->maintenance_and_monitoring_activities;
        $this->significant_change = $terrafundProgrammeSubmission->significant_change;
        $this->percentage_survival_to_date = $terrafundProgrammeSubmission->percentage_survival_to_date;
        $this->survival_comparison = $terrafundProgrammeSubmission->survival_comparison;
        $this->survival_calculation = $terrafundProgrammeSubmission->survival_calculation;
        $this->ft_women = $terrafundProgrammeSubmission->ft_women;
        $this->ft_men = $terrafundProgrammeSubmission->ft_men;
        $this->ft_youth = $terrafundProgrammeSubmission->ft_youth;
        $this->ft_total = $terrafundProgrammeSubmission->ft_total;
        $this->pt_women = $terrafundProgrammeSubmission->pt_women;
        $this->pt_men = $terrafundProgrammeSubmission->pt_men;
        $this->pt_youth = $terrafundProgrammeSubmission->pt_youth;
        $this->pt_total = $terrafundProgrammeSubmission->pt_total;
        $this->seasonal_women = $terrafundProgrammeSubmission->seasonal_women;
        $this->seasonal_men = $terrafundProgrammeSubmission->seasonal_men;
        $this->seasonal_youth = $terrafundProgrammeSubmission->seasonal_youth;
        $this->seasonal_total = $terrafundProgrammeSubmission->seasonal_total;
        $this->volunteer_women = $terrafundProgrammeSubmission->volunteer_women;
        $this->volunteer_men = $terrafundProgrammeSubmission->volunteer_men;
        $this->volunteer_youth = $terrafundProgrammeSubmission->volunteer_youth;
        $this->volunteer_total = $terrafundProgrammeSubmission->volunteer_total;
        $this->people_annual_income_increased = $terrafundProgrammeSubmission->people_annual_income_increased;
        $this->people_knowledge_skills_increased = $terrafundProgrammeSubmission->people_knowledge_skills_increased;
        $this->lessons_learned = $terrafundProgrammeSubmission->lessons_learned;
        $this->planted_trees = $terrafundProgrammeSubmission->planted_trees;
        $this->new_jobs_created = $terrafundProgrammeSubmission->new_jobs_created;
        $this->new_jobs_description = $terrafundProgrammeSubmission->new_jobs_description;
        $this->new_volunteers = $terrafundProgrammeSubmission->new_volunteers;
        $this->volunteers_work_description = $terrafundProgrammeSubmission->volunteers_work_description;
        $this->full_time_jobs_35plus = $terrafundProgrammeSubmission->full_time_jobs_35plus;
        $this->part_time_jobs_35plus = $terrafundProgrammeSubmission->part_time_jobs_35plus;
        $this->volunteer_35plus = $terrafundProgrammeSubmission->volunteer_35plus;
        $this->smallholder_beneficiaries = $terrafundProgrammeSubmission->smallholder_beneficiaries;
        $this->beneficiaries = $terrafundProgrammeSubmission->beneficiaries;
        $this->beneficiaries_description = $terrafundProgrammeSubmission->beneficiaries_description;
        $this->women_beneficiaries = $terrafundProgrammeSubmission->women_beneficiaries;
        $this->men_beneficiaries = $terrafundProgrammeSubmission->men_beneficiaries;
        $this->beneficiaries_35plus = $terrafundProgrammeSubmission->beneficiaries_35plus;
        $this->youth_beneficiaries = $terrafundProgrammeSubmission->youth_beneficiaries;
        $this->large_scale_beneficiaries = $terrafundProgrammeSubmission->large_scale_beneficiaries;
        $this->beneficiaries_income_increase = $terrafundProgrammeSubmission->beneficiaries_income_increase;
        $this->income_increase_description = $terrafundProgrammeSubmission->income_increase_description;
        $this->beneficiaries_skills_knowledge_increase = $terrafundProgrammeSubmission->beneficiaries_skills_knowledge_increase;
        $this->skills_knowledge_description = $terrafundProgrammeSubmission->skills_knowledge_description;
        $this->terrafund_programme_id = $terrafundProgrammeSubmission->terrafund_programme_id;
        $this->shared_drive_link = $this->handleSharedDriveLink($terrafundProgrammeSubmission->shared_drive_link);
        $this->photos = $this->getPhotos($terrafundProgrammeSubmission);
        $this->other_additional_documents = $this->getDocuments($terrafundProgrammeSubmission);
        $this->created_at = $terrafundProgrammeSubmission->created_at;
        $this->updated_at = $terrafundProgrammeSubmission->updated_at;
        $this->due_at = data_get($terrafundProgrammeSubmission->terrafundDueSubmission, 'due_at', null);
    }

    private function getPhotos($submission)
    {
        $resources = [];
        foreach ($submission->terrafundPhotosFiles as $terrafundFile) {
            $resources[] = new TerrafundFileResource($terrafundFile);
        }

        return $resources;
    }

    private function getDocuments($submission)
    {
        $resources = [];
        foreach ($submission->terrafundDocumentsFiles as $terrafundFile) {
            $resources[] = new TerrafundFileResource($terrafundFile);
        }

        return $resources;
    }

    private function handleSharedDriveLink($link)
    {
        if (empty($link)) {
            return null;
        }

        return Str::startsWith(strtolower($link), ['http://', 'https://']) ? $link : 'https://' . $link;
    }
}
