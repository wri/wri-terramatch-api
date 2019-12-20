<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pitch;

class PitchPolicy extends Policy
{
    public function create(?User $user, ?Pitch $model = null): bool
    {
        return $this->isFullUser($user) && $this->hasApprovedOrganisation($user);
    }

    public function read(?User $user, ?Pitch $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isFullUser($user);
    }

    public function update(?User $user, ?Pitch $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }

    public function search(?User $user, ?Pitch $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function inspect(?User $user, ?Pitch $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
