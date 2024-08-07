<?php

namespace App\Policies;

use App\Models\Pitch as PitchModel;
use App\Models\V2\User as UserModel;

class PitchPolicy extends Policy
{
    public function create(?UserModel $user, ?PitchModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->hasApprovedOrganisation($user);
    }

    public function read(?UserModel $user, ?PitchModel $model = null): bool
    {
        if ($this->isVerifiedAdmin($user)) {
            return true;
        } elseif ($this->isFullUser($user)) {
            if ($this->isOwner($user, $model)) {
                return true;
            } else {
                $isVisible = ! in_array($model->visibility, ['archived', 'finished']);

                return $isVisible;
            }
        } else {
            return false;
        }
    }

    public function update(?UserModel $user, ?PitchModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model);
    }

    public function updateVisibility(?UserModel $user, ?PitchModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function search(?UserModel $user, ?PitchModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function inspect(?UserModel $user, ?PitchModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function readAllBy(?UserModel $user, ?PitchModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }
}
