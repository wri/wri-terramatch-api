<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TeamMember;

class TeamMemberPolicy extends Policy
{
    public function create(?User $user, ?TeamMember $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?User $user, ?TeamMember $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isFullUser($user);
    }

    public function update(?User $user, ?TeamMember $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function delete(?User $user, ?TeamMember $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

}
