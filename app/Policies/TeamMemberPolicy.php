<?php

namespace App\Policies;

use App\Models\TeamMember as TeamMemberModel;
use App\Models\User as UserModel;

class TeamMemberPolicy extends Policy
{
    public function create(?UserModel $user, ?TeamMemberModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?TeamMemberModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isFullUser($user);
    }

    public function update(?UserModel $user, ?TeamMemberModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function delete(?UserModel $user, ?TeamMemberModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
