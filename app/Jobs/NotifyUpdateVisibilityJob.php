<?php

namespace App\Jobs;

use App\Mail\UpdateVisibility;
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

class NotifyUpdateVisibilityJob implements ShouldQueue
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
        $class = get_class($this->model);
        if (! in_array($class, [\App\Models\Offer::class, \App\Models\Pitch::class])) {
            throw new Exception();
        }
        $this->notifyUsers(explode_pop('\\', $class), $this->model->id, $this->model->organisation_id);
    }

    private function notifyUsers(String $model, Int $id, Int $organisationId)
    {
        $pushService = App::make(\App\Services\PushService::class);
        $users = UserModel::where('organisation_id', '=', $organisationId)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(new UpdateVisibility($model, $id));
            }
            $pushService->sendPush(
                $user,
                "Update your project's funding status",
                'update_visibility',
                $model,
                $id
            );
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'Update',
                'body' => "Update your project's funding status",
                'action' => 'update_visibility',
                'referenced_model' => $model,
                'referenced_model_id' => $id,
            ]);
            $notification->saveOrFail();
        }
    }
}
