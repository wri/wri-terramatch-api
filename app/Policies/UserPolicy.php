<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends Policy
{
    public function create(?User $user, ?User $model = null): bool
    {
        return $this->isGuest($user);
    }

    public function accept(?User $user, ?User $model = null): bool
    {
        return $this->isGuest($user);
    }

    public function invite(?User $user, ?User $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?User $user, ?User $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isUser($user) && $this->isOwner($user, $model));
    }

    public function update(?User $user, ?User $model = null): bool
    {
        return $this->isUser($user) && $this->isOwner($user, $model);
    }

    public function assign(?User $user, ?User $model = null): bool
    {
        return $this->isFullUser($user) && $this->isFullUser($model) && $user->organisation_id == $model->organisation_id;
    }
}
