<?php

namespace App\Policies;

use App\Models\SiteSubmission as SiteSubmissionModel;
use App\Models\User as UserModel;

class SiteSubmissionPolicy extends Policy
{
    public function create(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function readAll(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function update(?UserModel $user, ?SiteSubmissionModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isVerifiedAdmin($user);
    }

    public function delete(?UserModel $user, ?SiteSubmissionModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function addBoundary(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function approve(?UserModel $user, ?SiteSubmissionModel $model = null): bool
    {
        return $this->isAdmin($user);
    }

    public function createFile(?UserModel $user, ?SiteSubmissionModel $model = null): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user);
    }

    public function export(?UserModel $user): bool
    {
        return $this->isAdmin($user);
    }

    public function exportOwned(?UserModel $user, ?SiteSubmissionModel $model = null): bool
    {
        if ($this->isAdmin($user) || $this->isTerrafundAdmin($user)) {
            return true;
        }

        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
