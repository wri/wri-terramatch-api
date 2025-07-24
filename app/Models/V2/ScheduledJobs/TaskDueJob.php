<?php

namespace App\Models\V2\ScheduledJobs;

use Carbon\Carbon;
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
}
