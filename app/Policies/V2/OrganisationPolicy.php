<?php

namespace App\Policies\V2;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use App\Policies\Policy;

class OrganisationPolicy extends Policy
{
    public function create(?User $user, ?Organisation $model = null): bool
    {
        return ! $this->isGuest($user) && ! $this->isVerifiedAdmin($user) && ! $this->isTerrafundAdmin($user);
    }

    public function read(?User $user, ?Organisation $model = null): bool
    {
        if ($this->isTerrafundAdmin($user) || $this->isVerifiedAdmin($user)) {
            return true;
        }

        if (! empty($model)) {
            return in_array($model->id, $user->all_my_organisations->pluck('id')->toArray());
        }

        return $this->isFullUser($user);
    }

    public function readAll(?User $user, ?Organisation $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function update(?User $user, ?Organisation $model = null): bool
    {
        return $this->isAdminOwnerOrMonitoringPartner($user, $model);
    }

    public function delete(?User $user, ?Organisation $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function submit(?User $user, ?Organisation $model = null): bool
    {
        return ($this->isFullUser($user) && in_array($model->id, $user->all_my_organisations->pluck('id')->toArray()));
    }

    public function approveReject(?User $user, ?Organisation $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function requestJoinExisting(?User $user, ?Organisation $model = null): bool
    {
        return $this->isUser($user);
    }

    public function listUsers(?User $user, ?Organisation $model = null): bool
    {
        return $this->isAdminOwnerOrMonitoringPartner($user, $model);
    }

    public function approveRejectUser(?User $user, ?Organisation $model = null): bool
    {
        return $this->isAdminOwnerOrMonitoringPartner($user, $model);
    }

    public function viewUsers(?User $user, ?Organisation $model = null): bool
    {
        return $this->isAdminOwnerOrMonitoringPartner($user, $model);
    }

    public function listing(?User $user, ?Organisation $model = null): bool
    {
        return ! $this->isGuest($user);
    }

    public function uploadFiles(?User $user, ?Organisation $model = null): bool
    {
        return $this->isAdminOwnerOrMonitoringPartner($user, $model);
    }

    public function updateFileProperties(?User $user, ?Organisation $model = null): bool
    {
        return $this->isAdminOwnerOrMonitoringPartner($user, $model);
    }

    public function deleteFiles(?User $user, ?Organisation $model = null): bool
    {
        return $this->isAdminOwnerOrMonitoringPartner($user, $model);
    }

    private function isAdminOwnerOrMonitoringPartner(?User $user, ?Organisation $model = null)
    {
        return $this->isTerrafundAdmin($user) ||
            $this->isVerifiedAdmin($user) ||
            (
                $this->isFullUser($user) &&
                (
                    $user->organisation_id == $model->id || in_array($user->id, $model->usersApproved->pluck('id')->toArray())
                )
            );
    }

    public function export(?User $user, ?Organisation $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }
}
