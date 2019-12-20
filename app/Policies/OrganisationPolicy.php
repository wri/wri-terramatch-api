<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Organisation;

class OrganisationPolicy extends Policy
{
    public function create(?User $user, ?Organisation $model = null): bool
    {
        return $this->isOrphanedUser($user);
    }

    public function read(?User $user, ?Organisation $model = null): bool
    {
        return $this->isUser($user) || $this->isVerifiedAdmin($user);
    }

    public function inspect(?User $user, ?Organisation $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function update(?User $user, ?Organisation $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function readAll(?User $user, ?Organisation $model = null): bool
    {
        return $this->isVerifiedAdmin($user);
    }
}
