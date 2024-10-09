<?php

namespace App\Policies;

use App\Models\Organisation as OrganisationModel;
use App\Models\V2\User as UserModel;

class OrganisationPolicy extends Policy
{
    public function create(?UserModel $user, ?OrganisationModel $model = null): bool
    {
        return $this->isOrphanedUser($user);
    }

    public function read(?UserModel $user, ?OrganisationModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $this->isFullUser($user);
    }

    public function inspect(?UserModel $user, ?OrganisationModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || ($this->isUser($user) && $this->isOwner($user, $model));
    }

    public function update(?UserModel $user, ?OrganisationModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function readAll(?UserModel $user, ?OrganisationModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function readAllBy(?UserModel $user, ?OrganisationModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }
}
