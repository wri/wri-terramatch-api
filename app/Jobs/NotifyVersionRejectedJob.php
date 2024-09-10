<?php

namespace App\Jobs;

use App\Helpers\NotificationHelper;
use App\Mail\VersionRejected as VersionRejectedMail;
use App\Models\Interfaces\Version as VersionModel;
use App\Models\Notification as NotificationModel;
use App\Models\Pitch as PitchModel;
use App\Models\V2\User as UserModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class NotifyVersionRejectedJob implements ShouldQueue
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
                $organisationId = PitchModel::findOrFail($id)->organisation_id;

                break;
            case 'CarbonCertificationVersion':
            case 'PitchDocumentVersion':
            case 'RestorationMethodMetricVersion':
            case 'TreeSpeciesVersion':
                $model = 'Pitch';
                $id = $this->version->parent->pitch_id;
                $organisationId = PitchModel::findOrFail($id)->organisation_id;

                break;
            default:
                throw new Exception();
        }
        $explanation =
            (array_flip(config('data.rejected_reasons'))[$this->version->rejected_reason])
            . ': '
            . $this->version->rejected_reason_body;
        $identifier = md5($model . '_' . $id . '_REJECTED');
        if (NotificationHelper::isDuplicate($identifier)) {
            return;
        }
        $this->notifyUsers($model, $id, $organisationId, $explanation);
    }

    private function notifyUsers(String $model, Int $id, Int $organisationId, String $explanation)
    {
        $pushService = App::make(\App\Services\PushService::class);
        $users = UserModel::where('organisation_id', '=', $organisationId)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            if ($user->is_subscribed) {
                Mail::to($user->email_address)->send(new VersionRejectedMail($model, $id, $explanation, $user));
            }
            $pushService->sendPush(
                $user,
                'Your changes have been rejected',
                'version_rejected',
                $model,
                $id
            );
            $notification = new NotificationModel([
                'user_id' => $user->id,
                'title' => 'Rejected',
                'body' => 'Your changes have been rejected',
                'action' => 'version_rejected',
                'referenced_model' => $model,
                'referenced_model_id' => $id,
            ]);
            $notification->saveOrFail();
        }
    }
}
