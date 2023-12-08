<?php

namespace App\Policies\V2;

use App\Models\User;
use App\Models\V2\User as UserModel;
use App\Policies\Policy;

class UserPolicy extends Policy
{
    public function create(?User $user, ?UserModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->can('users-manage');
    }

    public function read(?User $user, ?UserModel $model = null): bool
    {
        return $user->can('users-manage') || $this->isVerifiedAdmin($user) || ($this->isUser($user) && $this->isOwner($user, $model));
    }

    public function readAll(?User $user, ?UserModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->can('users-manage');
    }

    public function update(?User $user, ?UserModel $model = null): bool
    {
        return ($this->isUser($user) && $this->isOwner($user, $model)) || ($this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user)) || $user->can('users-manage');
    }

    public function delete(?User $user, ?UserModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->can('users-manage');
    }

    public function export(?User $user, ?UserModel $model = null): bool
    {
        return $user->can('users-manage') || $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function resetPassword(?User $user, ?UserModel $model = null): bool
    {
        return $user->can('users-manage') || $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->id === $model->id;
    }

    public function verify(?User $user, ?UserModel $model = null): bool
    {
        return $user->can('users-manage') || $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->id === $model->id;
    }
}
