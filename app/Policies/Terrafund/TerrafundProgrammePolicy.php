<?php

namespace App\Policies\Terrafund;

use App\Models\Terrafund\TerrafundProgramme as TerrafundProgrammeModel;
use App\Models\User as UserModel;
use App\Policies\Policy;

class TerrafundProgrammePolicy extends Policy
{
    public function create(?UserModel $user): bool
    {
        return ($this->isFullUser($user) && $user->is_terrafund_user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function readAll(?UserModel $user): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function readAllPersonal(?UserModel $user): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function readAllForOrg(?UserModel $user): bool
    {
        return $this->isAdmin($user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function update(?UserModel $user, ?TerrafundProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function read(?UserModel $user, ?TerrafundProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function invite(?UserModel $user, ?TerrafundProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function createTree(?UserModel $user, ?TerrafundProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteTree(?UserModel $user, ?TerrafundProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function createFile(?UserModel $user, ?TerrafundProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteFile(?UserModel $user, ?TerrafundProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deletePartner(?UserModel $user, ?TerrafundProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function createNursery(?UserModel $user, ?TerrafundProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function createSubmission(?UserModel $user, ?TerrafundProgrammeModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function export(?UserModel $user): bool
    {
        return $this->isTerrafundAdmin($user) || $this->isAdmin($user);
    }

    public function exportOwned(?UserModel $user, ?TerrafundProgrammeModel $model = null): bool
    {
        if ($this->isAdmin($user) || $this->isTerrafundAdmin($user)) {
            return true;
        }

        return ($this->isFullUser($user) && $this->isOwner($user, $model));
    }
}
