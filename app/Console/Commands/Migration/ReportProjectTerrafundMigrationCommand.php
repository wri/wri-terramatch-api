<?php

namespace App\Console\Commands\Migration;

use App\Models\Terrafund\TerrafundProgramme;
use App\Models\Terrafund\TerrafundProgrammeSubmission;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Console\Command;

class ReportProjectTerrafundMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'v2migration:report-project-terrafund {--fresh} {--timestamps}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate Terrafund Submission Data only to  V2 Project reports';

    public function handle()
    {
        echo('* * * Started * * * ' . $this->description . chr(10));
        $count = 0;
        $created = 0;

        if ($this->option('fresh')) {
            ProjectReport::truncate();
        }

        TerrafundProgrammeSubmission::chunk(500, function ($chunk) use (&$count, &$created) {
            foreach ($chunk as $submission) {
                $count++;
                $map = $this->mapValues($submission);

                $report = ProjectReport::create($map);
                $created++;

                if ($this->option('timestamps')) {
                    $report->created_at = $submission->created_at;
                    $report->updated_at = $submission->updated_at;
                    $report->save();
                }
            }
        });

        echo('Processed:' . $count . ', Created: ' . $created . chr(10));
        echo('- - - Finished - - - ' . chr(10));
    }

    private function mapValues(TerrafundProgrammeSubmission $submission): array
    {
        $data = [
            'old_model' => TerrafundProgrammeSubmission::class,
            'old_id' => $submission->id,
            'framework_key' => 'terrafund',

            'due_at' => $this->handleDueAt($submission),
            'status' => ReportStatusStateMachine::AWAITING_APPROVAL,
            'landscape_community_contribution' => data_get($submission, 'landscape_community_contribution'),
            'top_three_successes' => data_get($submission, 'top_three_successes'),
            'challenges_faced' => data_get($submission, 'challenges_and_lessons'),
            'lessons_learned' => data_get($submission, 'lessons_learned'),
            'maintenance_and_monitoring_activities' => data_get($submission, 'maintenance_and_monitoring_activities'),
            'significant_change' => data_get($submission, 'significant_change'),
            'pct_survival_to_date' => data_get($submission, 'percentage_survival_to_date'),
            'survival_calculation' => data_get($submission, 'survival_calculation'),
            'survival_comparison' => data_get($submission, 'survival_comparison'),
            'ft_women' => data_get($submission, 'ft_women'),
            'ft_men' => data_get($submission, 'ft_men'),
            'ft_youth' => data_get($submission, 'ft_youth'),
            'ft_smallholder_farmers' => data_get($submission, 'ft_smallholder_farmers'),
            'ft_total' => data_get($submission, 'ft_total'),
            'pt_women' => data_get($submission, 'pt_women'),
            'pt_men' => data_get($submission, 'pt_men'),
            'pt_youth' => data_get($submission, 'pt_youth'),
            'pt_non_youth' => data_get($submission, 'part_time_jobs_35plus'),
            'pt_smallholder_farmers' => data_get($submission, 'pt_smallholder_farmers'),
            'pt_total' => data_get($submission, 'pt_total'),
            'seasonal_women' => data_get($submission, 'seasonal_women'),
            'seasonal_men' => data_get($submission, 'seasonal_men'),
            'seasonal_youth' => data_get($submission, 'seasonal_youth'),
            'seasonal_smallholder_farmers' => data_get($submission, 'seasonal_smallholder_farmers'),
            'seasonal_total' => data_get($submission, 'seasonal_total'),
            'volunteer_women' => data_get($submission, 'volunteer_women'),
            'volunteer_men' => data_get($submission, 'volunteer_men'),
            'volunteer_youth' => data_get($submission, 'volunteer_youth'),
            'volunteer_smallholder_farmers' => data_get($submission, 'volunteer_smallholder_farmers'),
            'volunteer_total' => data_get($submission, 'volunteer_total'),
            'shared_drive_link' => data_get($submission, 'shared_drive_link'),
            'planted_trees' => data_get($submission, 'planted_trees'),
            'new_jobs_created' => data_get($submission, 'new_jobs_created'),
            'new_jobs_description' => data_get($submission, 'new_jobs_description'),
            'new_volunteers' => data_get($submission, 'new_volunteers'),
            'volunteers_work_description' => data_get($submission, 'volunteers_work_description'),
            'volunteer_non_youth' => data_get($submission, 'volunteer_35plus'),
            'beneficiaries' => data_get($submission, 'beneficiaries'),
            'beneficiaries_description' => data_get($submission, 'beneficiaries_description'),
            'beneficiaries_women' => data_get($submission, 'women_beneficiaries'),
            'beneficiaries_men' => data_get($submission, 'men_beneficiaries'),
            'beneficiaries_non_youth' => data_get($submission, 'beneficiaries_35plus'),
            'beneficiaries_youth' => data_get($submission, 'youth_beneficiaries'),
            'beneficiaries_smallholder' => data_get($submission, 'smallholder_beneficiaries'),
            'beneficiaries_large_scale' => data_get($submission, 'large_scale_beneficiaries'),
            'beneficiaries_income_increase_description' => data_get($submission, 'income_increase_description'),
            'beneficiaries_skills_knowledge_increase_description' => data_get($submission, 'skills_knowledge_description'),
            /*
            'beneficiaries_skills_knowledge_increase' => data_get($submission, 'beneficiaries_skills_knowledge_increase'),
            'people_knowledge_skills_increased' => data_get($submission, 'people_knowledge_skills_increased'),
             */
            'beneficiaries_skills_knowledge_increase' => data_get($submission, 'people_knowledge_skills_increased'),
            'beneficiaries_income_increase' => $this->handleBeneficiariesIncomeIncrease($submission),


            'submitted_at' => data_get($submission, 'created_at'),

        ];

        $project = Project::where('old_model', TerrafundProgramme::class)
            ->where('old_id', $submission->terrafund_programme_id)
            ->first();

        if (! empty($project)) {
            $data['project_id'] = $project->id;
        }

        return $data;
    }

    private function handleDueAt(TerrafundProgrammeSubmission $submission)
    {
        if (empty($submission->terrafundDueSubmission)) {
            return null;
        }

        return $submission->terrafundDueSubmission->due_at;
    }

    private function handleBeneficiariesIncomeIncrease(TerrafundProgrammeSubmission $submission)
    {
        if (empty($submission->terrafundDueSubmission)) {
            return null;
        }
        $bii = $submission->beneficiaries_income_increase ?? 0;
        $paii = $submission->people_annual_income_increased ?? 0;

        return $bii + $paii;
    }
}
