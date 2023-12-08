<?php

namespace App\Jobs;

use App\Models\Notification as NotificationModel;
use App\Models\Terrafund\TerrafundProgramme;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyTerrafundReportReminderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $user;

    private $terrafundProgramme;

    public function __construct(User $user, TerrafundProgramme $terrafundProgramme)
    {
        $this->terrafundProgramme = $terrafundProgramme;
        $this->user = $user;
    }

    public function handle()
    {
        $notification = new NotificationModel([
            'user_id' => $this->user->id,
            'title' => 'Terrafund Report Reminder',
            'body' => 'Terrafund reports are due in a month',
            'action' => 'terrafund_report_reminder',
            'referenced_model' => 'TerrafundProgramme',
            'referenced_model_id' => $this->terrafundProgramme->id,
            'hidden_from_app' => true,
        ]);
        $notification->saveOrFail();
    }
}
