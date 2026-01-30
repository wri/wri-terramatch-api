<?php

namespace App\Models\V2\ScheduledJobs;

use Carbon\Carbon;
use Parental\HasParent;

/**
 * @property string $framework_key
 */
class ReportReminderJob extends ScheduledJob
{
    use HasParent;

    public static function createReportReminder(Carbon $execution_time, string $framework_key): ReportReminderJob
    {
        return self::create([
            'execution_time' => $execution_time,
            'task_definition' => [
                'framework_key' => $framework_key,
            ],
        ]);
    }
}
