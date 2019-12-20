<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Admin;

class AdminPolicy extends Policy
{
    public function accept(?User $user, ?Admin $model = null): bool
    {
        return $this->isGuest($user);
    }

    public function invite(?User $user, ?Admin $model = null): bool
    {
        return $this->isVerifiedAdmin($user);
    }

    public function read(?User $user, ?Admin $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isAdmin($user) && $this->isOwner($user, $model));
    }

    public function readAll(?User $user, ?Admin $model = null): bool
    {
        return $this->isVerifiedAdmin($user);
    }

    public function update(?User $user, ?Admin $model = null): bool
    {
        return $this->isAdmin($user) && $this->isOwner($user, $model);
    }
}
