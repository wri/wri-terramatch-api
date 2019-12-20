<?php

namespace App\Services;

use App\Mail\InterestShown;
use App\Mail\Match;
use App\Mail\VersionApproved;
use App\Mail\VersionCreated;
use App\Mail\VersionRejected;
use App\Models\Admin as AdminModel;
use App\Models\User as UserModel;
use App\Models\Contracts\NamedEntity;
use App\Models\Contracts\Version;
use App\Models\Interest as InterestModel;
use App\Models\Match as MatchModel;
use App\Models\Notification as NotificationModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;
use Exception;

class NotificationService
{
    private $pushService = null;
    private $adminModel = null;
    private $userModel = null;
    private $notificationModel = null;

    const INTEREST_SHOWN_ACTION = 'INTEREST_SHOWN';
    const MATCH_ACTION = 'MATCH';
    const VERSION_CREATED_ACTION = 'VERSION_CREATED';
    const VERSION_APPROVED_ACTION = 'VERSION_APPROVED';
    const VERSION_REJECTED_ACTION = 'VERSION_REJECTED';

    public function __construct(
        PushService $pushService,
        AdminModel $adminModel,
        UserModel $userModel,
        NotificationModel $notificationModel
    ) {
        $this->pushService = $pushService;
        $this->adminModel = $adminModel;
        $this->userModel = $userModel;
        $this->notificationModel = $notificationModel;
    }

    public function notifyInterest(InterestModel $interestModel): void
    {
        if (!$this->implementsNamedEntity($interestModel)) {
            throw new InvalidArgumentException();
        }
        $organisationIds = $this->getOrganisationIds($interestModel);
        $users = $this->userModel
            ->whereIn("organisation_id", $organisationIds)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            Mail::to($user->email_address)->send(new InterestShown());
            $this->pushService->sendInterestShownPush($user);
            $this->createNotificationAndSaveOrFail(
                $user->id,
                "Interest",
                "Someone has shown interest in you",
                self::INTEREST_SHOWN_ACTION,
                $interestModel->getEntityName(),
                $interestModel->id
            );
        }
    }

    public function notifyMatch(MatchModel $matchModel): void
    {
        if (!$this->implementsNamedEntity($matchModel)) {
            throw new InvalidArgumentException();
        }
        $organisationIds = $this->getOrganisationIds($matchModel);
        $users = $this->userModel
            ->whereIn("organisation_id", $organisationIds)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            Mail::to($user->email_address)->send(new Match($user));
            $this->pushService->sendMatchPush($user);
            $this->createNotificationAndSaveOrFail(
                $user->id,
                "Match",
                "Someone has matched with you",
                self::MATCH_ACTION,
                $matchModel->getEntityName(),
                $matchModel->id
            );
        }
        $admins = $this->adminModel
            ->admin()->accepted()->verified()
            ->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email_address)->send(new Match($admin));
            $this->pushService->sendMatchPush($admin);
            $this->createNotificationAndSaveOrFail(
                $admin->id,
                "Match",
                "Successful match",
                self::MATCH_ACTION,
                $matchModel->getEntityName(),
                $matchModel->id
            );
        }
    }

    public function notifyVersionCreated(Model $model): void
    {
        if (!$this->implementsVersion($model) || !$this->implementsNamedEntity($model)) {
            throw new InvalidArgumentException();
        }
        $admins = $this->adminModel
            ->admin()->accepted()->verified()
            ->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email_address)->send(new VersionCreated($model->getEntityName()));
            $this->pushService->sendVersionCreatedPush($admin);
            $this->createNotificationAndSaveOrFail(
                $admin->id,
                "Changes",
                "Changes requiring your approval",
                self::VERSION_CREATED_ACTION,
                $model->getEntityName(),
                $model->id
            );
        }
    }

    public function notifyVersionApproved(Model $model): void
    {
        if (!$this->implementsVersion($model) || !$this->implementsNamedEntity($model)) {
            throw new InvalidArgumentException();
        }
        $organisationIds = $this->getOrganisationIds($model);
        $users = $this->userModel
            ->whereIn("organisation_id", $organisationIds)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            Mail::to($user->email_address)->send(new VersionApproved($model->getEntityName(), $model->id));
            $this->pushService->sendVersionApprovedPush($user);
            $this->createNotificationAndSaveOrFail(
                $user->id,
                "Approved",
                "Your changes have been approved",
                self::VERSION_APPROVED_ACTION,
                $model->getEntityName(),
                $model->id
            );
        }
    }

    public function notifyVersionRejected(Model $model): void
    {
        if (!$this->implementsVersion($model) || !$this->implementsNamedEntity($model)) {
            throw new InvalidArgumentException();
        }
        $organisationIds = $this->getOrganisationIds($model);
        $users = $this->userModel
            ->whereIn("organisation_id", $organisationIds)
            ->user()->accepted()->verified()
            ->get();
        foreach ($users as $user) {
            Mail::to($user->email_address)->send(new VersionRejected($model->getEntityName(), $model->id));
            $this->pushService->sendVersionRejectedPush($user);
            $this->createNotificationAndSaveOrFail(
                $user->id,
                "Rejected",
                "Your changes have been rejected",
                self::VERSION_REJECTED_ACTION,
                $model->getEntityName(),
                $model->id
            );
        }
    }

    private function createNotificationAndSaveOrFail(
        int $userId,
        string $title,
        string $body,
        string $action = null,
        string $referenced_type = null,
        int $referenced_action_id = null
    ): void
    {
        $notification = $this->notificationModel->newInstance();
        $notification->user_id = $userId;
        $notification->title = $title;
        $notification->body = $body;
        $notification->action = $action;
        $notification->referenced_type = $referenced_type;
        $notification->referenced_action_id = $referenced_action_id;
        $notification->saveOrFail();
    }

    private function implementsVersion($model)
    {
        return $model instanceof Version;
    }

    private function implementsNamedEntity($model)
    {
        return $model instanceof NamedEntity;
    }

    private function getOrganisationIds(Model $model): array
    {
        switch (get_class($model)) {
            case "App\\Models\\Match":
                $parent = App::make("App\\Models\\Interest")->findOrFail($model->primary_interest_id);
                $grandFather = App::make("App\\Models\\Offer")->findOrFail($parent->offer_id);
                $grandMother = App::make("App\\Models\\Pitch")->findOrFail($parent->pitch_id);
                return [$grandFather->organisation_id, $grandMother->organisation_id];
            case "App\\Models\\Interest":
                $name = $model->initiator == "offer" ? "Pitch" : "Offer";
                $key = lcfirst($name) . "_id";
                $parent = App::make("App\\Models\\" . $name)->findOrFail($model->{$key});
                return [$parent->organisation_id];
            case "App\\Models\\OrganisationDocumentVersion":
                $parent = App::make("App\\Models\\OrganisationDocument")->findOrFail($model->organisation_document_id);
                return [$parent->organisation_id];
            case "App\\Models\\OrganisationVersion":
                return [$model->organisation_id];
            case "App\\Models\\PitchVersion":
                $parent = App::make("App\\Models\\Pitch")->findOrFail($model->pitch_id);
                return [$parent->organisation_id];
            case "App\\Models\\CarbonCertificationVersion":
            case "App\\Models\\PitchDocumentVersion":
            case "App\\Models\\RestorationMethodMetricVersion":
            case "App\\Models\\TreeSpeciesVersion":
                $name = substr(explode_pop("\\", get_class($model)), 0, -7);
                $key = Str::snake($name) . "_id";
                $parent = App::make("App\\Models\\" . $name)->findOrFail($model->{$key});
                $grandParent = App::make("App\\Models\\Pitch")->findOrFail($parent->pitch_id);
                return [$grandParent->organisation_id];
            default:
                throw new Exception();
        }
    }
}
