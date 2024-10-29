<?php

namespace App\Console\Commands\OneOff;

use App\Models\V2\Action;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Tasks\Task;
use App\StateMachines\ReportStatusStateMachine;
use App\StateMachines\TaskStatusStateMachine;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateBackDatedReportsEnterprisesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'one-off:generate-backdated-reports-enterprises-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates backdated reports for enterprises framework';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $due_at = Carbon::createFromFormat('m', 7)->startOfMonth()->setDay(30)->setHours(5);
        $period_key = $due_at->year . '-' . $due_at->month;
        $framework_key = 'enterprises';
        Project::where('framework_key', $framework_key)
            ->chunkById(100, function ($projects) use ($framework_key, $period_key, $due_at) {
                foreach ($projects as $project) {
                    $this->createTask($project, $framework_key, $period_key, $due_at);
                }
            });
    }

    public function createTaks($project, $framework_key, $period_key, $due_at)
    {
        $task = Task::create([
            'organisation_id' => $project->organisation_id,
            'project_id' => $project->id,
            'status' => TaskStatusStateMachine::DUE,
            'period_key' => $period_key,
            'due_at' => $due_at,
        ]);

        $projectReport = $task->projectReport()->create([
            'framework_key' => $framework_key,
            'project_id' => $project->id,
            'status' => ReportStatusStateMachine::DUE,
            'due_at' => $due_at,
        ]);

        $hasSite = false;
        foreach ($project->sites as $site) {
            $hasSite = true;
            $task->siteReports()->create([
                'framework_key' => $framework_key,
                'site_id' => $site->id,
                'status' => ReportStatusStateMachine::DUE,
                'due_at' => $due_at,
            ]);
        }

        $hasNursery = false;
        foreach ($project->nurseries as $nursery) {
            $hasNursery = true;
            $task->nurseryReports()->create([
                'framework_key' => $framework_key,
                'nursery_id' => $nursery->id,
                'status' => ReportStatusStateMachine::DUE,
                'due_at' => $due_at,
            ]);
        }

        $labels = ['Project'];
        if ($hasSite) {
            $labels[] = 'site';
        }
        if ($hasNursery) {
            $labels[] = 'nursery';
        }
        $message = printf(
            '%s %s available',
            implode(', ', $labels),
            count($labels) > 1 ? 'reports' : 'report'
        );

        Action::create([
            'status' => Action::STATUS_PENDING,
            'targetable_type' => ProjectReport::class,
            'targetable_id' => $projectReport->id,
            'type' => Action::TYPE_NOTIFICATION,
            'title' => 'Project report',
            'sub_title' => '',
            'text' => $message,
            'project_id' => $project->id,
            'organisation_id' => $project->organisation_id,
        ]);
    }
}
