<?php

namespace App\Models\V2\ScheduledJobs;

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
                'frameworkKey' => $frameworkKey,
                'dueAt' => $dueAt,
            ],
        ]);
    }

    public function scopeFramework(Builder $query, string $frameworkKey): Builder
    {
        return $query->whereJsonContains('task_definition->frameworkKey', $frameworkKey)
            ->orderBy('execution_time');
    }

    public function getFrameworkKeyAttribute(): string
    {
        return $this->task_definition['frameworkKey'];
    }

    public function getDueAtAttribute(): Carbon
    {
        return Carbon::parse($this->task_definition['dueAt']);
    }
}
