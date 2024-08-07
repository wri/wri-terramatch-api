<?php

namespace App\Jobs;

use App\Mail\ProgressUpdateCreated as ProgressUpdateCreatedMail;
use App\Models\Notification as NotificationModel;
use App\Models\ProgressUpdate as ProgressUpdateModel;
use App\Models\V2\User as UserModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyProgressUpdateCreatedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $progressUpdate;

    public function __construct(ProgressUpdateModel $progressUpdate)
    {
        $this->progressUpdate = $progressUpdate;
    }

    public function handle()
    {
        $this->notifyUsers(
            $this->progressUpdate->monitoring->matched->interest->offer->organisation_id,
            $this->progressUpdate->id,
            $this->progressUpdate->monitoring->matched->interest->pitch->approved_version->name
        );
    }

    private function notifyUsers(Int $organisationId, Int $progressUpdateId, String $pitchName)
    {
        $users = UserModel::where('organisation_id', '=', $organisationId)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(
                    new ProgressUpdateCreatedMail($progressUpdateId, $pitchName)
                );
            }
            /*
            $pushService->sendPush(
                $user,
                "You have received a new progress update from one of your projects",
                "progress_update_created",
                "ProgressUpdate",
                $progressUpdateId
            );
            */
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'Report Received',
                'body' => 'You have received a new progress update from one of your projects',
                'action' => 'progress_update_created',
                'referenced_model' => 'ProgressUpdate',
                'referenced_model_id' => $progressUpdateId,
                'hidden_from_app' => true,
            ]);
            $notification->saveOrFail();
        }
    }
}
