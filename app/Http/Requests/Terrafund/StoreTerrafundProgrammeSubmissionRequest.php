<?php

namespace App\Http\Requests\Terrafund;

use Illuminate\Foundation\Http\FormRequest;

class StoreTerrafundProgrammeSubmissionRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        return [
            'shared_drive_link' => 'nullable|url|max:255',
            'landscape_community_contribution' => 'required|string|between:1,65000',
            'top_three_successes' => 'required|string|between:1,10000',
            'maintenance_and_monitoring_activities' => 'required|string|between:1,10000',
            'significant_change' => 'required|string|between:1,10000',
            'percentage_survival_to_date' => 'nullable|integer|between:0,100',
            'survival_calculation' => 'nullable|string|between:1,5000',
            'survival_comparison' => 'nullable|string|between:1,5000',
            'terrafund_programme_id' => 'required|integer|exists:terrafund_programmes,id',
            'ft_women' => 'nullable|between:0,4000000000',
            'ft_men' => 'nullable|between:0,4000000000',
            'ft_youth' => 'nullable|between:0,4000000000',
            'ft_total' => 'nullable|between:0,4000000000',
            'pt_women' => 'nullable|between:0,4000000000',
            'pt_men' => 'nullable|between:0,4000000000',
            'pt_youth' => 'nullable|between:0,4000000000',
            'pt_total' => 'nullable|between:0,4000000000',
            'volunteer_women' => 'nullable|between:0,4000000000',
            'volunteer_men' => 'nullable|between:0,4000000000',
            'volunteer_youth' => 'nullable|between:0,4000000000',
            'volunteer_total' => 'nullable|between:0,4000000000',
            'people_annual_income_increased' => 'required|between:0,4000000000',
            'people_knowledge_skills_increased' => 'required|between:0,4000000000',
            'challenges_faced' => 'nullable|string|max:65000',
            'lessons_learned' => 'nullable|string|max:65000',
            'planted_trees' => 'nullable|boolean',
            'new_jobs_created' => 'nullable|integer|between:0,4000000000',
            'new_jobs_description' => 'nullable|string|max:65000',
            'new_volunteers' => 'nullable|integer|between:0,4000000000',
            'volunteers_work_description' => 'nullable|string|max:65000',
            'full_time_jobs_35plus' => 'nullable|integer|between:0,4000000000',
            'part_time_jobs_35plus' => 'nullable|integer|between:0,4000000000',
            'volunteer_35plus' => 'nullable|integer|between:0,4000000000',
            'beneficiaries' => 'nullable|integer|between:0,4000000000',
            'beneficiaries_description' => 'nullable|string|max:65000',
            'women_beneficiaries' => 'nullable|integer|between:0,4000000000',
            'men_beneficiaries' => 'nullable|integer|between:0,4000000000',
            'beneficiaries_35plus' => 'nullable|integer|between:0,4000000000',
            'youth_beneficiaries' => 'nullable|integer|between:0,4000000000',
            'smallholder_beneficiaries' => 'nullable|integer|between:0,4000000000',
            'large_scale_beneficiaries' => 'nullable|integer|between:0,4000000000',
            'beneficiaries_income_increase' => 'nullable|integer|between:0,4000000000',
            'income_increase_description' => 'nullable|string|max:65000',
            'beneficiaries_skills_knowledge_increase' => 'nullable|string|max:65000',
            'skills_knowledge_description' => 'nullable|string|max:65000',
            'terrafund_due_submission_id' => '',
        ];
    }
}
