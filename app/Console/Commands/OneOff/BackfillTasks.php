<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Tasks\Task;
use App\StateMachines\TaskStatusStateMachine;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Relations\Relation;

class BackfillTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:backfill-tasks {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates tasks to associate orphaned project and site reports.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // As of the creation of this command, there are 70 orphaned, valid project reports, and they're all in PPC, so
        // we can ignore nursery reports as part of this work. That also means that there aren't enough orphaned reports
        // to bother with batching this initial fetch.
        $projects = [];
        foreach (ProjectReport::where('task_id', null)->whereNot('due_at', null)->get() as $projectReport) {
            $projects[$projectReport->project_id]['project_reports'][] = [
                'due_at' => $projectReport->due_at,
                'uuid' => $projectReport->uuid,
            ];
        }

        $totalMissing = 0;
        $totalToDelete = 0;
        $totalMissingDueDates = 0;
        $totalFound = 0;
        foreach ($projects as $projectId => &$data) {
            /** @var Project $project */
            $project = Project::find($projectId);
            $data['project_name'] = $project->name;
            $data['project_uuid'] = $project->uuid;
            foreach ($data['project_reports'] as $projectReport) {
                $minDate = (clone $projectReport['due_at'])->subDays(5);
                $maxDate = (clone $projectReport['due_at'])->addDays(5);
                $foundQuery = $project
                    ->siteReports()
                    ->where('task_id', null)
                    ->where('due_at', '>=', $minDate)
                    ->where('due_at', '<=', $maxDate);
                $data['associatable_site_reports'] = (clone $foundQuery)->count();
                $data['associatable_site_reports_due'] = array_merge(
                    $data['associatable_site_reports_due'] ?? [],
                    (clone $foundQuery)->select('due_at')->distinct()->pluck('due_at')->toArray()
                );
                $totalFound += $data['associatable_site_reports'];

                $this->createTask($projectReport['uuid'], $foundQuery);
            }

            $missingQuery = $project
                ->siteReports()
                ->where('task_id', null)
                ->whereNotIn('due_at', $data['associatable_site_reports_due']);
            $data['count_missing'] = (clone $missingQuery)->count();
            $missingDueDates = (clone $missingQuery)->select('due_at')->distinct()->pluck('due_at');
            $totalMissing += $data['count_missing'];

            $data['count_can_associate_with_deleted'] = 0;
            foreach ($missingDueDates as $dueDate) {
                $minDate = (clone $dueDate)->subDays(5);
                $maxDate = (clone $dueDate)->addDays(5);
                if ($project
                    ->reports()
                    ->withTrashed()
                    ->whereNot('deleted_at', null)
                    ->where('due_at', '>=', $minDate)
                    ->where('due_at', '<=', $maxDate)
                    ->exists()) {
                    $data['count_can_associate_with_deleted'] += $project
                        ->siteReports()
                        ->where('due_at', $dueDate)
                        ->count();
                    $data['due_dates_with_deleted_project_report'][] = $dueDate;
                } else {
                    $totalMissingDueDates++;
                    $data['missing_due_dates'][] = $dueDate;
                }
            }

            $totalMissing -= $data['count_can_associate_with_deleted'];
            $totalToDelete += $data['count_can_associate_with_deleted'];
        }

        $this->info(json_encode([
            'totals' => [
                'associatable_site_reports' => $totalFound,
                'unaccounted_for_site_reports' => $totalMissing,
                'unaccounted_for_site_report_due_dates' => $totalMissingDueDates,
                'site_reports_matching_a_deleted_project_report' => $totalToDelete,
            ],
            'details' => array_values($projects),
        ], JSON_PRETTY_PRINT));
    }

    protected function createTask(string $projectReportUuid, Relation $siteReports): void
    {
        if ($this->option('dry-run')) {
            return;
        }

        $this->info('Creating task for project report ' . $projectReportUuid . '...');

        $projectReport = ProjectReport::isUuid($projectReportUuid)->first();
        $dueDate = $projectReport->due_at;
        $task = Task::create([
            'organisation_id' => $projectReport->organisation->id,
            'project_id' => $projectReport->project_id,
            'status' => TaskStatusStateMachine::DUE,
            'period_key' => $dueDate->year . '-' . $dueDate->month,
            'due_at' => $dueDate,
        ]);
        $projectReport->update(['task_id' => $task->id]);

        // Set all the site reports to use the exact same due date as the project report / task
        $this->info('Associating ' . (clone $siteReports)->count() . ' site reports...');
        $siteReports->update(['task_id' => $task->id, 'due_at' => $dueDate]);

        $this->info('Project report ' . $projectReportUuid . " complete\n");
    }
}
