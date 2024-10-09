<?php

namespace App\Jobs;

use App\Mail\UpcomingProgressUpdate as UpcomingProgressUpdateMail;
use App\Models\Monitoring as MonitoringModel;
use App\Models\Notification as NotificationModel;
use App\Models\V2\User as UserModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyUpcomingProgressUpdateJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $monitoring;

    public function __construct(MonitoringModel $monitoring)
    {
        $this->monitoring = $monitoring;
    }

    public function handle()
    {
        $this->notifyUsers(
            $this->monitoring->matched->interest->pitch->organisation_id,
            $this->monitoring->id,
            $this->monitoring->matched->interest->pitch->approved_version->name
        );
    }

    private function notifyUsers(Int $organisationId, Int $monitoringId, String $pitchName)
    {
        $users = UserModel::where('organisation_id', '=', $organisationId)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(
                    new UpcomingProgressUpdateMail($monitoringId, $pitchName, $user)
                );
            }
            /*
            $pushService->sendPush(
                $user,
                "You are due to submit a progress update report for one of your projects",
                "upcoming_progress_update",
                "Monitoring",
                $monitoringId
            );
            */
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'Report Due',
                'body' => 'You are due to submit a progress update report for one of your projects',
                'action' => 'upcoming_progress_update',
                'referenced_model' => 'Monitoring',
                'referenced_model_id' => $monitoringId,
                'hidden_from_app' => true,
            ]);
            $notification->saveOrFail();
        }
    }
}
