<?php

namespace App\Policies\Terrafund;

use App\Models\Terrafund\TerrafundSite;
use App\Models\V2\User as UserModel;
use App\Policies\Policy;

class TerrafundSitePolicy extends Policy
{
    public function create(?UserModel $user): bool
    {
        return ($this->isFullUser($user) && $user->is_terrafund_user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function readMy(?UserModel $user): bool
    {
        return ($this->isFullUser($user) && $user->is_terrafund_user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function readAllTreeSpecies(?UserModel $user): bool
    {
        return ($this->isFullUser($user) && $user->is_terrafund_user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function update(?UserModel $user, ?TerrafundSite $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function read(?UserModel $user, ?TerrafundSite $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function createSubmission(?UserModel $user, ?TerrafundSite $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function createFile(?UserModel $user, ?TerrafundSite $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteFile(?UserModel $user, ?TerrafundSite $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function exportOwned(?UserModel $user, ?TerrafundSite $model = null): bool
    {
        if ($this->isAdmin($user) || $this->isTerrafundAdmin($user)) {
            return true;
        }

        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
