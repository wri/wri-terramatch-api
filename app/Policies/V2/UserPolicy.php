<?php

namespace App\Policies\V2;

use App\Models\V2\User;
use App\Policies\Policy;

class UserPolicy extends Policy
{
    public function create(?User $user, ?User $model = null): bool
    {
        return $this->isGuest($user) || $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->can('users-manage');
    }

    public function accept(?User $user, ?User $model = null): bool
    {
        return $this->isGuest($user);
    }

    public function invite(?User $user, ?User $model = null): bool
    {
        return $this->isFullUser($user) || $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function read(?User $user, ?User $model = null): bool
    {
        return $user->can('users-manage') || $this->isVerifiedAdmin($user) || ($this->isUser($user) && $this->isOwner($user, $model));
    }

    public function readAll(?User $user, ?User $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->can('users-manage');
    }

    public function resend(?User $user, ?User $model = null): bool
    {
        return $this->isVerifiedAdmin($user);
    }

    public function updateRole(?User $user, ?User $model = null): bool
    {
        return $this->isVerifiedAdmin($user) && ($user->id !== $model->id);
    }

    public function update(?User $user, ?User $model = null): bool
    {
        return ($this->isUser($user) && $this->isOwner($user, $model)) || ($this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user)) || $user->can('users-manage');
    }

    public function assign(?User $user, ?User $model = null): bool
    {
        $areColleagues = @$user->organisation_id == @$model->organisation_id;

        return $this->isFullUser($user) && $this->isFullUser($model) && $areColleagues;
    }

    public function delete(?User $user, ?User $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->can('users-manage');
    }

    public function deleteSelf(?User $user, $model = null): bool
    {
        return $user->id == $model->id;
    }

    public function export(?User $user, ?User $model = null): bool
    {
        return $user->can('users-manage') || $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function resetPassword(?User $user, ?User $model = null): bool
    {
        return $user->can('users-manage') || $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->id === $model->id;
    }

    public function verify(?User $user, ?User $model = null): bool
    {
        return $user->can('users-manage') || $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user) || $user->id === $model->id;
    }
}
