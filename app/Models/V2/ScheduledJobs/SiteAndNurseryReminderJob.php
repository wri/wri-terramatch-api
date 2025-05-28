<?php

namespace App\Models\V2\ScheduledJobs;

use Carbon\Carbon;
use Parental\HasParent;

/**
 * @property string $framework_key
 */
class SiteAndNurseryReminderJob extends ScheduledJob
{
    use HasParent;

    public static function createSiteAndNurseryReminder(Carbon $execution_time, string $framework_key): SiteAndNurseryReminderJob
    {
        return self::create([
            'execution_time' => $execution_time,
            'task_definition' => [
                'framework_key' => $framework_key,
            ],
        ]);
    }
}
