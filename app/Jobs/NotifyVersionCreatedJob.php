<?php

namespace App\Jobs;

use App\Helpers\NotificationHelper;
use App\Mail\VersionCreated as VersionCreatedMail;
use App\Models\Admin as AdminModel;
use App\Models\Interfaces\Version as VersionModel;
use App\Models\Notification as NotificationModel;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class NotifyVersionCreatedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Delete the job if its models no longer exist.
     *
     * @var bool
     */
    public $deleteWhenMissingModels = true;

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

                break;
            case 'OrganisationDocumentVersion':
                $model = 'Organisation';
                $id = $this->version->parent->organisation_id;

                break;
            case 'PitchVersion':
                $model = 'Pitch';
                $id = $this->version->pitch_id;

                break;
            case 'CarbonCertificationVersion':
            case 'PitchDocumentVersion':
            case 'RestorationMethodMetricVersion':
            case 'TreeSpeciesVersion':
                $model = 'Pitch';
                $id = $this->version->parent->pitch_id;

                break;
            default:
                throw new Exception();
        }
        $identifier = md5($model . '_' . $id . '_CREATED');
        if (NotificationHelper::isDuplicate($identifier)) {
            return;
        }
        $this->notifyAdmins($model, $id);
    }

    private function notifyAdmins(String $model, Int $id)
    {
        $admins = AdminModel::admin()->accepted()->verified()->get();
        foreach ($admins as $admin) {
            if ($admin->is_subscribed) {
                Mail::to($admin->email_address)->send(new VersionCreatedMail($model, $id));
            }
            $notification = new NotificationModel([
                'user_id' => $admin->id,
                'title' => 'Changes',
                'body' => 'Changes requiring your approval',
                'action' => 'version_created',
                'referenced_model' => $model,
                'referenced_model_id' => $id,
            ]);
            $notification->saveOrFail();
        }
    }
}
