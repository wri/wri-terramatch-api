<?php

namespace App\Jobs\V2;

use App\Models\V2\Action;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Tasks\Task;
use App\StateMachines\ReportStatusStateMachine;
use App\StateMachines\TaskStatusStateMachine;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CreateTaskDueJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private ?Carbon $dueDate;

    private $frameworkKey;

    public function __construct(string $frameworkKey, int $dueMonth = null)
    {
        $this->frameworkKey = $frameworkKey;

        if ($dueMonth) {
            $carbonDate = Carbon::createFromFormat('m', $dueMonth)->startOfMonth()->setDay(5);
            $this->dueDate = $carbonDate->isPast() ? $carbonDate->addYear() : $carbonDate;
        } else {
            $this->dueDate = Carbon::now()->addMonth()->startOfDay();
        }
    }

    public function handle()
    {
        Project::where('framework_key', $this->frameworkKey)
            ->chunkById(100, function ($projects) {
                foreach ($projects as $project) {
                    $task = Task::create([
                        'organisation_id' => $project->organisation_id,
                        'project_id' => $project->id,
                        'status' => TaskStatusStateMachine::DUE,
                        'period_key' => $this->dueDate->year . '-' . $this->dueDate->month,
                        'due_at' => $this->dueDate,
                    ]);

                    $projectReport = $task->projectReport()->create([
                        'framework_key' => $this->frameworkKey,
                        'project_id' => $project->id,
                        'status' => ReportStatusStateMachine::DUE,
                        'due_at' => $this->dueDate,
                    ]);

                    $hasSite = false;
                    foreach ($project->sites as $site) {
                        $hasSite = true;
                        $task->siteReports()->create([
                            'framework_key' => $this->frameworkKey,
                            'site_id' => $site->id,
                            'status' => ReportStatusStateMachine::DUE,
                            'due_at' => $this->dueDate,
                        ]);
                    }

                    $hasNursery = false;
                    foreach ($project->nurseries as $nursery) {
                        $hasNursery = true;
                        $task->nurseryReports()->create([
                            'framework_key' => $this->frameworkKey,
                            'nursery_id' => $nursery->id,
                            'status' => ReportStatusStateMachine::DUE,
                            'due_at' => $this->dueDate,
                        ]);
                    }

                    $labels = ['Project'];
                    if ($hasSite) $labels[] = 'site';
                    if ($hasNursery) $labels[] = 'nursery';
                    $message = printf('%s %s available',
                        implode(', ', $labels),
                        count($labels) > 1 ? 'reports' : 'report');

                    Action::create([
                        'status' => Action::STATUS_PENDING,
                        'targetable_type' => ProjectReport::class,
                        'targetable_id' => $projectReport->id,
                        'type' => Action::TYPE_NOTIFICATION,
                        'title' => 'Project report',
                        'sub_title' => '',
                        'text' => $message,
                        'project_id' => $project->id,
                        'organisation_id' => data_get($project->organisation, 'id'),
                    ]);
                }
            });
    }
}
