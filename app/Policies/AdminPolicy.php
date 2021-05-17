<?php

namespace App\Policies;

use App\Models\User as UserModel;
use App\Models\Admin as AdminModel;

class AdminPolicy extends Policy
{
    public function accept(?UserModel $user, ?AdminModel $model = null): bool
    {
        return $this->isGuest($user);
    }

    public function invite(?UserModel $user, ?AdminModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user);
    }

    public function read(?UserModel $user, ?AdminModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isAdmin($user) && $this->isOwner($user, $model));
    }

    public function readAll(?UserModel $user, ?AdminModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user);
    }

    public function update(?UserModel $user, ?AdminModel $model = null): bool
    {
        return $this->isAdmin($user) && $this->isOwner($user, $model);
    }
}
