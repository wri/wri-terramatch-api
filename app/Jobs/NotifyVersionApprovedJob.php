<?php

namespace App\Jobs;

use App\Helpers\NotificationHelper;
use App\Mail\VersionApproved as VersionApprovedMail;
use App\Models\Interfaces\Version as VersionModel;
use App\Models\Notification as NotificationModel;
use App\Models\User as UserModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class NotifyVersionApprovedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private $version = null;

    public function __construct(VersionModel $version)
    {
        $this->version = $version;
    }

    public function handle()
    {
        switch ($this->version->getEntityName()) {
            case 'OrganisationVersion':
                $model = 'Organisation';
                $id = $this->version->organisation_id;
                $organisationId = $id;

                break;
            case 'OrganisationDocumentVersion':
                $model = 'Organisation';
                $id = $this->version->parent->organisation_id;
                $organisationId = $id;

                break;
            case 'PitchVersion':
                $model = 'Pitch';
                $id = $this->version->pitch_id;
                $organisationId = $this->version->pitch->organisation->id;

                break;
            case 'CarbonCertificationVersion':
            case 'PitchDocumentVersion':
            case 'RestorationMethodMetricVersion':
            case 'TreeSpeciesVersion':
                $model = 'Pitch';
                $id = $this->version->parent->pitch_id;
                $organisationId = $this->version->parent->pitch->organisation->id;

                break;
            default:
                throw new Exception();
        }
        $identifier = md5($model . '_' . $id . '_APPROVED');
        if (NotificationHelper::isDuplicate($identifier)) {
            return;
        }
        $this->notifyUsers($model, $id, $organisationId);
    }

    private function notifyUsers(String $model, Int $id, Int $organisationId)
    {
        $pushService = App::make(\App\Services\PushService::class);
        $users = UserModel::where('organisation_id', '=', $organisationId)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(new VersionApprovedMail($model, $id));
            }
            $pushService->sendPush(
                $user,
                'Your changes have been approved',
                'version_approved',
                $model,
                $id
            );
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'Approved',
                'body' => 'Your changes have been approved',
                'action' => 'version_approved',
                'referenced_model' => $model,
                'referenced_model_id' => $id,
            ]);
            $notification->saveOrFail();
        }
    }
}
