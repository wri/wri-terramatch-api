<?php

namespace App\Jobs;

use App\Mail\MatchMail;
use App\Models\Admin as AdminModel;
use App\Models\Interest as InterestModel;
use App\Models\Matched as MatchModel;
use App\Models\Notification as NotificationModel;
use App\Models\V2\User as UserModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class NotifyMatchJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $matched = null;

    public function __construct(MatchModel $matched)
    {
        $this->matched = $matched;
    }

    public function handle()
    {
        $interest = InterestModel::findOrFail($this->matched->primary_interest_id);
        $this->notifyUsers(
            'Funder',
            $this->matched->id,
            $interest->offer->organisation_id,
            $interest->pitch->approved_version->name
        );
        $this->notifyUsers(
            'Developer',
            $this->matched->id,
            $interest->pitch->organisation_id,
            $interest->offer->name
        );
        $this->notifyAdmins($this->matched->id, $interest->pitch->approved_version->name, $interest->offer->name);
    }

    private function notifyUsers(String $type, Int $id, Int $organisationId, String $name)
    {
        $pushService = App::make(\App\Services\PushService::class);
        $users = UserModel::where('organisation_id', '=', $organisationId)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(new MatchMail($type, $name));
            }
            $pushService->sendPush(
                $user,
                'Someone has matched with one of your projects',
                'match',
                'Match',
                $id
            );
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'Match',
                'body' => 'Someone has matched with one of your projects',
                'action' => 'match',
                'referenced_model' => 'Match',
                'referenced_model_id' => $id,
            ]);
            $notification->saveOrFail();
        }
    }

    private function notifyAdmins(Int $id, String $firstName, String $secondName)
    {
        $admins = AdminModel::admin()->accepted()->verified()->get();
        foreach ($admins as $admin) {
            if ($admin->is_subscribed) {
                Mail::to($admin->email_address)->send(new MatchMail('Admin', $firstName, $secondName));
            }
            $notification = new NotificationModel([
                'user_id' => $admin->id,
                'title' => 'Match',
                'body' => 'Match detected',
                'action' => 'match',
                'referenced_model' => 'Match',
                'referenced_model_id' => $id,
            ]);
            $notification->saveOrFail();
        }
    }
}
