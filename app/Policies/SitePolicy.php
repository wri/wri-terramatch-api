<?php

namespace App\Policies;

use App\Models\Site as SiteModel;
use App\Models\V2\User as UserModel;

class SitePolicy extends Policy
{
    public function create(?UserModel $user): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user);
    }

    public function readAllForUser(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function readAllTreeSpecies(?UserModel $user, ?SiteModel $model = null): bool
    {
        return $this->isFullUser($user) || $this->isOwner($user, $model) || $this->isAdmin($user);
    }

    public function update(?UserModel $user, ?SiteModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user);
    }

    public function read(?UserModel $user, ?SiteModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user);
    }

    public function readAll(?UserModel $user): bool
    {
        return $this->isAdmin($user);
    }

    public function addBoundary(?UserModel $user): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user);
    }

    public function createSubmission(?UserModel $user, ?SiteModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user);
    }

    public function pendingRead(?UserModel $user): bool
    {
        return $this->isFullUser($user) || $this->isVerifiedAdmin($user);
    }

    public function createFile(?UserModel $user, ?SiteModel $model = null): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user);
    }

    public function export(?UserModel $user): bool
    {
        return $this->isAdmin($user);
    }

    public function exportOwned(?UserModel $user, ?SiteModel $model = null): bool
    {
        if ($this->isAdmin($user) || $this->isTerrafundAdmin($user)) {
            return true;
        }

        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
