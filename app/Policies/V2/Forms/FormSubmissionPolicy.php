<?php

namespace App\Policies\V2\Forms;

use App\Models\V2\Forms\FormSubmission;
use App\Models\V2\User;
use App\Policies\Policy;

class FormSubmissionPolicy extends Policy
{
    public function create(?User $user, ?FormSubmission $model = null): bool
    {
        return ! $this->isGuest($user) && ! $this->isVerifiedAdmin($user) && ! $this->isTerrafundAdmin($user);
    }

    public function read(?User $user, ?FormSubmission $model = null): bool
    {
        return $this->isAdminOwnerOrMonitoringPartner($user, $model);
    }

    public function update(?User $user, ?FormSubmission $model = null): bool
    {
        return $this->isAdminOwnerOrMonitoringPartner($user, $model);
    }

    public function export(?User $user, ?FormSubmission $model = null): bool
    {
        return $this->isTerrafundAdmin($user) || $this->isVerifiedAdmin($user);
    }

    public function delete(?User $user, ?FormSubmission $model = null): bool
    {
        return $this->isAdminOwnerOrMonitoringPartner($user, $model);
    }

    private function isAdminOwnerOrMonitoringPartner(?User $user, ?FormSubmission $model = null)
    {
        return $this->isTerrafundAdmin($user) ||
            $this->isVerifiedAdmin($user) ||
            (
                $this->isFullUser($user) &&
                (
                    in_array($model->organisation->id, $user->all_my_organisations->pluck('id')->toArray())
                )
            );
    }
}
