<?php

namespace App\Policies\Terrafund;

use App\Models\Terrafund\TerrafundNursery as TerrafundNurseryModel;
use App\Models\V2\User as UserModel;
use App\Policies\Policy;

class TerrafundNurseryPolicy extends Policy
{
    public function create(?UserModel $user): bool
    {
        return $this->isFullUser($user) && $user->is_terrafund_user;
    }

    public function readMy(?UserModel $user): bool
    {
        return $this->isFullUser($user) && $user->is_terrafund_user;
    }

    public function createFile(?UserModel $user, ?TerrafundNurseryModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function createTree(?UserModel $user, ?TerrafundNurseryModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function readAllTreeSpecies(?UserModel $user): bool
    {
        return $this->isFullUser($user) && $user->is_terrafund_user;
    }

    public function createSubmission(?UserModel $user, ?TerrafundNurseryModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteFile(?UserModel $user, ?TerrafundNurseryModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function deleteTree(?UserModel $user, ?TerrafundNurseryModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function read(?UserModel $user, ?TerrafundNurseryModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function update(?UserModel $user, ?TerrafundNurseryModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function exportOwned(?UserModel $user, ?TerrafundNurseryModel $model = null): bool
    {
        if ($this->isAdmin($user) || $this->isTerrafundAdmin($user)) {
            return true;
        }

        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
