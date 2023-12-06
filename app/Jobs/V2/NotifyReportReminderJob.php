<?php

namespace App\Jobs\V2;

use App\Models\Notification as NotificationModel;
use App\Models\V2\Projects\Project;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyReportReminderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private User $user;

    private Project $project;

    private string $frameworkKey;

    public function __construct(User $user, Project $project, string $frameworkKey)
    {
        $this->project = $project;
        $this->user = $user;
        $this->frameworkKey = $frameworkKey;
    }

    public function handle()
    {
        $notification = new NotificationModel([
            'user_id' => $this->user->id,
            'title' => ucfirst($this->frameworkKey) . ' Report Reminder',
            'body' => ucfirst($this->frameworkKey) . ' reports are due in a month',
            'action' => $this->frameworkKey . '_report_reminder',
            'referenced_model' => 'Project',
            'referenced_model_id' => $this->project->id,
            'hidden_from_app' => true,
        ]);
        $notification->saveOrFail();
    }
}
