<?php

namespace App\Jobs;

use App\Mail\InterestShown as InterestShownMail;
use App\Models\Interest as InterestModel;
use App\Models\Notification as NotificationModel;
use App\Models\V2\User as UserModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class NotifyInterestJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $interest = null;

    private $user = null;

    public function __construct(InterestModel $interest, $user)
    {
        $this->interest = $interest;
        $this->user = $user;
    }

    public function handle()
    {
        switch ($this->interest->initiator) {
            case 'offer':
                $model = 'Offer';
                $name = $this->interest->offer->name;
                $id = $this->interest->offer_id;
                $organisationId = $this->interest->pitch->organisation_id;

                break;
            case 'pitch':
                $model = 'Pitch';
                $name = $this->interest->pitch->approved_version->name;
                $id = $this->interest->pitch_id;
                $organisationId = $this->interest->offer->organisation_id;

                break;
            default:
                throw new Exception();
        }
        $this->notifyUsers($model, $name, $id, $organisationId);
    }

    private function notifyUsers(String $model, String $name, Int $id, Int $organisationId)
    {
        $pushService = App::make(\App\Services\PushService::class);
        $users = UserModel::where('organisation_id', '=', $organisationId)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(new InterestShownMail($model, $name, $id, $user));
            }
            $pushService->sendPush(
                $user,
                'Someone has shown interest in one of your projects',
                'interest_shown',
                $model,
                $id
            );
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'Interest',
                'body' => 'Someone has shown interest in one of your projects',
                'action' => 'interest_shown',
                'referenced_model' => $model,
                'referenced_model_id' => $id,
            ]);
            $notification->saveOrFail();
        }
    }
}
