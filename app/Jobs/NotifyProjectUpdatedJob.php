<?php

namespace App\Jobs;

use App\Helpers\NotificationHelper;
use App\Mail\ProjectUpdated as ProjectUpdatedMail;
use App\Models\Interest as InterestModel;
use App\Models\Notification as NotificationModel;
use App\Models\V2\User as UserModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class NotifyProjectUpdatedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $model = null;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function handle()
    {
        $model = explode_pop('\\', get_class($this->model));
        $id = $this->model->id;
        switch ($model) {
            case 'Offer':
                $matches = InterestModel::where('has_matched', '=', 1)
                    ->where('initiator', '=', 'pitch')
                    ->where('offer_id', '=', $id)
                    ->get();
                $interests = InterestModel::where('has_matched', '=', 0)
                    ->where('initiator', '=', 'pitch')
                    ->where('offer_id', '=', $id)
                    ->get();

                break;
            case 'Pitch':
                $matches = InterestModel::where('has_matched', '=', 1)
                    ->where('initiator', '=', 'offer')
                    ->where('pitch_id', '=', $id)
                    ->get();
                $interests = InterestModel::where('has_matched', '=', 0)
                    ->where('initiator', '=', 'offer')
                    ->where('pitch_id', '=', $id)
                    ->get();

                break;
            default:
                throw new Exception();
        }
        $organisationIds = [
            'matches' => arr_uv($matches->pluck('organisation_id')->toArray()),
            'interested' => arr_dv(
                arr_uv($matches->pluck('organisation_id')->toArray()),
                arr_uv($interests->pluck('organisation_id')->toArray())
            ),
        ];
        $identifier = md5($model . '_' . $id . '_UPDATED');
        if (NotificationHelper::isDuplicate($identifier)) {
            return;
        }
        $this->notifyMatchedUsers($organisationIds['matches'], $model, $id);
        $this->notifyInterestedUsers($organisationIds['interested'], $model, $id);
    }

    private function notifyMatchedUsers(array $organisationIds, String $model, Int $id)
    {
        $pushService = App::make(\App\Services\PushService::class);
        $users = UserModel::whereIn('organisation_id', $organisationIds)
            ->user()
            ->accepted()
            ->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(new ProjectUpdatedMail('Matched', $model, $id));
            }
            $pushService->sendPush(
                $user,
                "A project you've matched with has changed",
                'project_changed',
                $model,
                $id
            );
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'Changed',
                'body' => "A project you've matched with has changed",
                'action' => 'project_changed',
                'referenced_model' => $model,
                'referenced_model_id' => $id,
            ]);
            $notification->saveOrFail();
        }
    }

    private function notifyInterestedUsers(array $organisationIds, String $model, Int $id)
    {
        $pushService = App::make(\App\Services\PushService::class);
        $users = UserModel::whereIn('organisation_id', $organisationIds)
            ->user()
            ->accepted()
            ->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(new ProjectUpdatedMail('Interest', $model, $id));
            }
            $pushService->sendPush(
                $user,
                "A project you're interested in has changed",
                'project_changed',
                $model,
                $id
            );
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'Changed',
                'body' => "A project you're interested in has changed",
                'action' => 'project_changed',
                'referenced_model' => $model,
                'referenced_model_id' => $id,
            ]);
            $notification->saveOrFail();
        }
    }
}
