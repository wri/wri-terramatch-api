<?php

namespace App\Policies;

use App\Models\Programme as ProgrammeModel;
use App\Models\V2\User as UserModel;

class ProgrammePolicy extends Policy
{
    public function create(?UserModel $user): bool
    {
        return ($this->isFullUser($user) && $user->is_ppc_user) || $this->isAdmin($user);
    }

    public function readAll(?UserModel $user): bool
    {
        return $this->isVerifiedAdmin($user);
    }

    public function readAllPersonal(?UserModel $user): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function readAllTreeSpecies(?UserModel $user): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function read(?UserModel $user, ?ProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function update(?UserModel $user, ?ProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user);
    }

    public function invite(?UserModel $user, ?ProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user);
    }

    public function accept(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function readAllPartners(?UserModel $user, ?ProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user);
    }

    public function createTreeSpecies(?UserModel $user, ?ProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user);
    }

    public function removeUser(?UserModel $user, ?ProgrammeModel $model = null): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user);
    }

    public function addBoundary(?UserModel $user): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user);
    }

    public function pendingRead(?UserModel $user): bool
    {
        return $this->isFullUser($user) || $this->isVerifiedAdmin($user);
    }

    public function createFile(?UserModel $user, ?ProgrammeModel $model = null): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user);
    }

    public function export(?UserModel $user): bool
    {
        return $this->isAdmin($user);
    }

    public function exportOwned(?UserModel $user, ?ProgrammeModel $model = null): bool
    {
        if ($this->isAdmin($user) || $this->isTerrafundAdmin($user)) {
            return true;
        }

        return ($this->isFullUser($user) && $this->isOwner($user, $model));
    }
}
