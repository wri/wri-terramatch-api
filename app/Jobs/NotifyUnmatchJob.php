<?php

namespace App\Jobs;

use App\Mail\Unmatch as UnmatchMail;
use App\Models\Notification as NotificationModel;
use App\Models\Offer as OfferModel;
use App\Models\Pitch as PitchModel;
use App\Models\V2\User;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class NotifyUnmatchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $offerModel = null;

    private $pitch = null;

    private $initiator = null;

    public function __construct(OfferModel $offerModel, PitchModel $pitch, String $initiator)
    {
        $this->offerModel = $offerModel;
        $this->pitch = $pitch;
        $this->initiator = $initiator;
    }

    public function handle()
    {
        switch ($this->initiator) {
            case 'Offer':
                $organisationId = $this->pitch->organisation_id;
                $firstName = $this->offerModel->name;
                $secondName = $this->pitch->approved_version->name;

                break;
            case 'Pitch':
                $organisationId = $this->offerModel->organisation_id;
                $firstName = $this->pitch->approved_version->name;
                $secondName = $this->offerModel->name;

                break;
            default:
                throw new Exception();
        }
        $this->notifyUsers($organisationId, $firstName);
        $this->notifyAdmins($firstName, $secondName);
    }

    private function notifyUsers(Int $organisationId, String $name)
    {
        $pushService = App::make(\App\Services\PushService::class);
        $users = User::where('organisation_id', '=', $organisationId)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(new UnmatchMail('User', $name));
            }
            $pushService->sendPush(
                $user,
                'Someone has unmatched with one of your projects',
                'unmatch'
            );
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'Unmatch',
                'body' => 'Someone has unmatched with one of your projects',
                'action' => 'unmatch',
                'referenced_model' => null,
                'referenced_model_id' => null,
            ]);
            $notification->saveOrFail();
        }
    }

    private function notifyAdmins(String $firstName, String $secondName)
    {
        $admins = User::admin()->accepted()->verified()->get();
        foreach ($admins as $admin) {
            if ($admin->is_subscribed) {
                Mail::to($admin->email_address)->send(new UnmatchMail('Admin', $firstName, $secondName));
            }
            $notification = new NotificationModel([
                'user_id' => $admin->id,
                'title' => 'Unmatch',
                'body' => 'Unmatch detected',
                'action' => 'unmatch',
                'referenced_model' => null,
                'referenced_model_id' => null,
            ]);
            $notification->saveOrFail();
        }
    }
}
