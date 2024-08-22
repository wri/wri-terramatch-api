<?php

namespace App\Policies\Terrafund;

use App\Models\Terrafund\TerrafundNurserySubmission as TerrafundNurserySubmissionModel;
use App\Models\V2\User as UserModel;
use App\Policies\Policy;

class TerrafundNurserySubmissionPolicy extends Policy
{
    public function createTree(?UserModel $user, ?TerrafundNurserySubmissionModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function createFile(?UserModel $user, ?TerrafundNurserySubmissionModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteFile(?UserModel $user, ?TerrafundNurserySubmissionModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function update(?UserModel $user, ?TerrafundNurserySubmissionModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function read(?UserModel $user, ?TerrafundNurserySubmissionModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function readAllForUser(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function export(?UserModel $user): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function exportOwned(?UserModel $user, ?TerrafundNurserySubmissionModel $model = null): bool
    {
        if ($this->isAdmin($user) || $this->isTerrafundAdmin($user)) {
            return true;
        }

        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
