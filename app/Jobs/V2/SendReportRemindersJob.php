<?php

namespace App\Jobs\V2;

use App\Mail\TerrafundReportReminder;
use App\Models\V2\Projects\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendReportRemindersJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $frameworkKey;

    public function __construct(string $frameworkKey)
    {
        $this->frameworkKey = $frameworkKey;
    }

    public function handle()
    {
        Project::query()
            ->where('framework_key', '=', $this->frameworkKey)
            ->where(function ($query) {
                $query->whereHas('sites')
                    ->orWhereHas('nurseries');
            })->chunkById(100, function ($projects) {
                $projects->each(function ($project) {
                    if ($project->users->count()) {
                        Mail::to($project->users->pluck('email_address'))->queue(new TerrafundReportReminder($project->id));
                        $project->users->each(function ($user) use ($project) {
                            NotifyReportReminderJob::dispatch($user, $project, $this->frameworkKey);
                        });
                    }
                });
            });
    }
}
