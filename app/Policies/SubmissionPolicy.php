<?php

namespace App\Policies;

use App\Models\Submission as SubmissionModel;
use App\Models\V2\User as UserModel;

class SubmissionPolicy extends Policy
{
    public function create(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function readAll(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function update(?UserModel $user, ?SubmissionModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isVerifiedAdmin($user);
    }

    public function delete(?UserModel $user, ?SubmissionModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function approve(?UserModel $user, ?SubmissionModel $model = null): bool
    {
        return $this->isAdmin($user);
    }

    public function createFile(?UserModel $user, ?SubmissionModel $model = null): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user);
    }

    public function export(?UserModel $user): bool
    {
        return $this->isAdmin($user);
    }

    public function exportOwned(?UserModel $user, ?SubmissionModel $model = null): bool
    {
        if ($this->isAdmin($user) || $this->isTerrafundAdmin($user)) {
            return true;
        }

        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
