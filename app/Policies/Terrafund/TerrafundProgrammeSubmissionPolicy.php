<?php

namespace App\Policies\Terrafund;

use App\Models\Terrafund\TerrafundProgrammeSubmission as TerrafundProgrammeSubmissionModel;
use App\Models\User as UserModel;
use App\Policies\Policy;

class TerrafundProgrammeSubmissionPolicy extends Policy
{
    public function createFile(?UserModel $user, ?TerrafundProgrammeSubmissionModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteFile(?UserModel $user, ?TerrafundProgrammeSubmissionModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function readAllForUser(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?TerrafundProgrammeSubmissionModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function update(?UserModel $user, ?TerrafundProgrammeSubmissionModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function export(?UserModel $user): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function exportOwned(?UserModel $user, ?TerrafundProgrammeSubmissionModel $model = null): bool
    {
        if ($this->isAdmin($user) || $this->isTerrafundAdmin($user)) {
            return true;
        }

        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
