<?php

namespace App\Policies;

use App\Models\Draft as DraftModel;
use App\Models\V2\User as UserModel;

class DraftPolicy extends Policy
{
    public function create(?UserModel $user, ?DraftModel $model = null): bool
    {
        return $this->isVerifiedUser($user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function read(?UserModel $user, ?DraftModel $model = null): bool
    {
        return ($this->isVerifiedUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function readAll(?UserModel $user, ?DraftModel $model = null): bool
    {
        return $this->isVerifiedUser($user) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function update(?UserModel $user, ?DraftModel $model = null): bool
    {
        return ($this->isVerifiedUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function delete(?UserModel $user, ?DraftModel $model = null): bool
    {
        return ($this->isVerifiedUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function publish(?UserModel $user, ?DraftModel $model = null): bool
    {
        return ($this->isVerifiedUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user) || $this->isTerrafundAdmin($user);
    }
}
