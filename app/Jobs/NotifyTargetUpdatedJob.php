<?php

namespace App\Jobs;

use App\Mail\TargetUpdated as TargetUpdatedMail;
use App\Models\Notification as NotificationModel;
use App\Models\Target as TargetModel;
use App\Models\V2\User as UserModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class NotifyTargetUpdatedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $target;

    private $model;

    public function __construct(TargetModel $target, String $model)
    {
        $this->target = $target;
        switch ($model) {
            case 'Offer':
            case 'Pitch':
                $this->model = $model;

                break;
            default:
                throw new InvalidArgumentException();
        }
    }

    public function handle()
    {
        $organisationId =
            $this->model == 'Offer' ?
                $this->target->monitoring->matched->interest->offer->organisation_id :
                $this->target->monitoring->matched->interest->pitch->organisation_id;
        $targetId = $this->target->id;
        $name = $this->target->monitoring->matched->interest->pitch->approved_version->name;
        $this->notifyUsers($organisationId, $targetId, $name);
    }

    private function notifyUsers(Int $organisationId, Int $targetId, String $name)
    {
        $users = UserModel::where('organisation_id', '=', $organisationId)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(new TargetUpdatedMail($targetId, $name, $user));
            }
            /*
            $pushService->sendPush(
                $user,
                "Your monitoring targets have been edited by your collaborator and need your review",
                "target_updated",
                "Target",
                $targetId
            );
            */
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'Monitoring Targets Need Review',
                'body' => 'Your monitoring targets have been edited by your collaborator and need your review',
                'action' => 'target_updated',
                'referenced_model' => 'Target',
                'referenced_model_id' => $targetId,
                'hidden_from_app' => true,
            ]);
            $notification->saveOrFail();
        }
    }
}
