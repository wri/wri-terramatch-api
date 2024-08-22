<?php

namespace App\Policies\Terrafund;

use App\Models\Terrafund\TerrafundSiteSubmission;
use App\Models\V2\User as UserModel;
use App\Policies\Policy;
use Illuminate\Auth\Access\HandlesAuthorization;

class TerrafundSiteSubmissionPolicy extends Policy
{
    use HandlesAuthorization;

    public function read(?UserModel $user, ?TerrafundSiteSubmission $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function readAllForUser(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function update(?UserModel $user, ?TerrafundSiteSubmission $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function createTree(?UserModel $user, ?TerrafundSiteSubmission $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function createNoneTree(?UserModel $user, ?TerrafundSiteSubmission $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteNoneTree(?UserModel $user, ?TerrafundSiteSubmission $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteTree(?UserModel $user, ?TerrafundSiteSubmission $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function createFile(?UserModel $user, ?TerrafundSiteSubmission $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteFile(?UserModel $user, ?TerrafundSiteSubmission $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function createDisturbance(?UserModel $user, ?TerrafundSiteSubmission $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteDisturbance(?UserModel $user, ?TerrafundSiteSubmission $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function updateDisturbance(?UserModel $user, ?TerrafundSiteSubmission $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function export(?UserModel $user): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function exportOwned(?UserModel $user, ?TerrafundSiteSubmission $model = null): bool
    {
        if ($this->isAdmin($user) || $this->isTerrafundAdmin($user)) {
            return true;
        }

        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
