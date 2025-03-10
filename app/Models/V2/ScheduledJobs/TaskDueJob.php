<?php

namespace App\Models\V2\ScheduledJobs;

use App\Models\V2\Action;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Tasks\Task;
use App\StateMachines\EntityStatusStateMachine;
use App\StateMachines\ReportStatusStateMachine;
use App\StateMachines\TaskStatusStateMachine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Parental\HasParent;

/**
 * @property string $framework_key
 * @property Carbon $due_at
 * @property string $period_key
 */
class TaskDueJob extends ScheduledJob
{
    use HasParent;

    public static function createTaskDue(Carbon $executionTime, string $frameworkKey, Carbon $dueAt): TaskDueJob
    {
        return self::create([
            'execution_time' => $executionTime,
            'task_definition' => [
                'framework_key' => $frameworkKey,
                'due_at' => $dueAt,
            ],
        ]);
    }

    public function scopeFramework(Builder $query, string $frameworkKey): Builder
    {
        return $query->whereJsonContains('task_definition->framework_key', $frameworkKey)
            ->orderBy('execution_time');
    }

    public function getFrameworkKeyAttribute(): string
    {
        return $this->task_definition['framework_key'];
    }

    public function getDueAtAttribute(): Carbon
    {
        return Carbon::parse($this->task_definition['due_at']);
    }

    public function getPeriodKeyAttribute(): string
    {
        return $this->due_at->year . '-' . $this->due_at->month;
    }

    protected function performJob(): void
    {
        Project::where('framework_key', $this->framework_key)
            ->where('status', '!=', EntityStatusStateMachine::STARTED)
            ->chunkById(100, function ($projects) {
                foreach ($projects as $project) {
                    $this->createTask($project);
                }
            });
    }

    protected function createTask(Project $project): void
    {
        $existTask = Task::where('project_id', $project->id)
            ->where('due_at', $this->due_at)
            ->exists();
        if ($existTask) {
            return;
        }
        $task = Task::create([
            'organisation_id' => $project->organisation_id,
            'project_id' => $project->id,
            'status' => TaskStatusStateMachine::DUE,
            'period_key' => $this->period_key,
            'due_at' => $this->due_at,
        ]);

        $projectReport = $task->projectReport()->create([
            'framework_key' => $this->framework_key,
            'project_id' => $project->id,
            'status' => ReportStatusStateMachine::DUE,
            'due_at' => $this->due_at,
        ]);

        $hasSite = false;
        foreach ($project->nonDraftSites as $site) {
            $hasSite = true;
            $task->siteReports()->create([
                'framework_key' => $this->framework_key,
                'site_id' => $site->id,
                'status' => ReportStatusStateMachine::DUE,
                'due_at' => $this->due_at,
            ]);
        }

        $hasNursery = false;
        foreach ($project->nonDraftNurseries as $nursery) {
            $hasNursery = true;
            $task->nurseryReports()->create([
                'framework_key' => $this->framework_key,
                'nursery_id' => $nursery->id,
                'status' => ReportStatusStateMachine::DUE,
                'due_at' => $this->due_at,
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
