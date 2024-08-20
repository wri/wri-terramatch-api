<?php

namespace App\Policies\V2\Forms;

use App\Models\V2\Forms\Application;
use App\Models\V2\User;
use App\Policies\Policy;

class ApplicationPolicy extends Policy
{
    public function readAll(?User $user, ?Application $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function delete(?User $user, ?Application $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function read(?User $user, ?Application $model = null): bool
    {
        return $this->isAdminOwnerOrPartner($user, $model);
    }

    public function exportAll(?User $user, ?Application $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function viewOnlyMine(?User $user, ?Application $model = null): bool
    {
        return $this->isFullUser($user);
    }

    private function isAdminOwnerOrPartner(?User $user, ?Application $model = null)
    {
        if ($this->isTerrafundAdmin($user) || $this->isVerifiedAdmin($user)) {
            return true;
        }
        $organisation = $model->organisation;

        return $this->isFullUser($user) &&
            (
                $user->organisation_id == $organisation->id ||
                in_array(
                    $user->id,
                    $organisation->usersApproved->pluck('id')->toArray()
                )
            );
    }
}
