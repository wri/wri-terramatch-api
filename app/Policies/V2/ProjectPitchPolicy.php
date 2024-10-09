<?php

namespace App\Policies\V2;

use App\Models\V2\ProjectPitch;
use App\Models\V2\User;
use App\Policies\Policy;

class ProjectPitchPolicy extends Policy
{
    public function read(?User $user, ?ProjectPitch $model = null): bool
    {
        return $this->isAdminOrOwner($user, $model);
    }

    public function delete(?User $user, ?ProjectPitch $model = null): bool
    {
        return $this->isAdminOrOwner($user, $model);
    }

    public function submit(?User $user, ?ProjectPitch $model = null): bool
    {
        return $this->isAdminOrOwner($user, $model);
    }

    public function uploadFiles(?User $user, ?ProjectPitch $model = null): bool
    {
        return $this->isAdminOrOwner($user, $model);
    }

    public function updateFileProperties(?User $user, ?ProjectPitch $model = null): bool
    {
        return $this->isAdminOrOwner($user, $model);
    }

    public function deleteFiles(?User $user, ?ProjectPitch $model = null): bool
    {
        return $this->isAdminOrOwner($user, $model);
    }

    private function isAdminOrOwner(?User $user, ?ProjectPitch $model = null)
    {
        return $this->isVerifiedAdmin($user) ||
            (
                $this->isUser($user) &&
                in_array($model->organisation_id, $user->all_my_organisations->pluck('uuid')->toArray())
            );
    }
}
