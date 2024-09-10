<?php

namespace App\Models\V2\ScheduledJobs;

use App\Jobs\V2\NotifyReportReminderJob;
use App\Mail\TerrafundReportReminder;
use App\Models\V2\Projects\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
// use Parental\HasParent;

/**
 * @property string $framework_key
 */
class ReportReminderJob extends ScheduledJob
{
    // use HasParent;

    public static function createReportReminder(Carbon $execution_time, string $framework_key): ReportReminderJob
    {
        return self::create([
            'execution_time' => $execution_time,
            'task_definition' => [
                'framework_key' => $framework_key,
            ],
        ]);
    }

    public function getFrameworkKeyAttribute(): string
    {
        return $this->task_definition['framework_key'];
    }

    protected function performJob(): void
    {
        $user = Auth::user();
        Project::where('framework_key', $this->framework_key)
            ->where(function ($query) {
                $query->whereHas('sites')->orWhereHas('nurseries');
            })->chunkById(100, function ($projects) use ($user) {
                $projects->each(function ($project) use ($user) {
                    Mail::to($project->users->pluck('email_address'))->queue(new TerrafundReportReminder($project->id, $user->locale));
                    $project->users->each(function ($user) use ($project) {
                        NotifyReportReminderJob::dispatch($user, $project, $this->framework_key);
                    });
                });
            });
    }
}
