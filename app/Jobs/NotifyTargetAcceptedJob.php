<?php

namespace App\Jobs;

use App\Mail\TargetAccepted as TargetAcceptedMail;
use App\Models\Notification as NotificationModel;
use App\Models\Target as TargetModel;
use App\Models\V2\User as UserModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyTargetAcceptedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $target;

    private $model;

    public function __construct(TargetModel $target)
    {
        $this->target = $target;
    }

    public function handle()
    {
        $offerOrganisationId = $this->target->monitoring->matched->interest->offer->organisation_id;
        $pitchOrganisationId = $this->target->monitoring->matched->interest->pitch->organisation_id;
        $monitoringId = $this->target->monitoring->id;
        $targetId = $this->target->id;
        $name = $this->target->monitoring->matched->interest->pitch->approved_version->name;
        $this->notifyUsers($offerOrganisationId, $monitoringId, $targetId, $name);
        $this->notifyUsers($pitchOrganisationId, $monitoringId, $targetId, $name);
    }

    private function notifyUsers(Int $organisationId, Int $monitoringId, Int $targetId, String $name)
    {
        $users = UserModel::where('organisation_id', '=', $organisationId)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(new TargetAcceptedMail($monitoringId, $name));
            }
            /*
            $pushService->sendPush(
                $user,
                "Your monitoring targets have been approved for your project",
                "target_accepted",
                "Target",
                $targetId
            );
            */
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'Monitoring Targets Approved',
                'body' => 'Your monitoring targets have been approved for your project',
                'action' => 'target_accepted',
                'referenced_model' => 'Target',
                'referenced_model_id' => $targetId,
                'hidden_from_app' => true,
            ]);
            $notification->saveOrFail();
        }
    }
}
