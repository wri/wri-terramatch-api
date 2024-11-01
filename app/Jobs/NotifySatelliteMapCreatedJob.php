<?php

namespace App\Jobs;

use App\Mail\SatelliteMapCreated as SatelliteMapCreatedMail;
use App\Models\Notification as NotificationModel;
use App\Models\SatelliteMap as SatelliteMapModel;
use App\Models\V2\User as UserModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifySatelliteMapCreatedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $satelliteMap;

    public function __construct(SatelliteMapModel $satelliteMap)
    {
        $this->satelliteMap = $satelliteMap;
    }

    public function handle()
    {
        $this->notifyUsers(
            $this->satelliteMap->monitoring->matched->interest->offer->organisation_id,
            $this->satelliteMap->id,
            $this->satelliteMap->monitoring->matched->interest->offer->name
        );
        $this->notifyUsers(
            $this->satelliteMap->monitoring->matched->interest->pitch->organisation_id,
            $this->satelliteMap->id,
            $this->satelliteMap->monitoring->matched->interest->pitch->approved_version->name
        );
    }

    private function notifyUsers(Int $organisationId, Int $satelliteMapId, String $name)
    {
        $users = UserModel::where('organisation_id', '=', $organisationId)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(new SatelliteMapCreatedMail($satelliteMapId, $name, $user));
            }
            /*
            $pushService->sendPush(
                $user,
                "WRI has updated a remote sensing map for one of your projects",
                "satellite_map_created",
                "SatelliteMap",
                $satelliteMapId
            );
            */
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'New Remote Sensing Map',
                'body' => 'WRI has updated a remote sensing map for one of your projects',
                'action' => 'satellite_map_created',
                'referenced_model' => 'SatelliteMap',
                'referenced_model_id' => $satelliteMapId,
                'hidden_from_app' => true,
            ]);
            $notification->saveOrFail();
        }
    }
}
