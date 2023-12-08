<?php

namespace App\Policies;

use App\Models\User as UserModel;

class UserPolicy extends Policy
{
    public function create(?UserModel $user, ?UserModel $model = null): bool
    {
        return $this->isGuest($user) || $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function accept(?UserModel $user, ?UserModel $model = null): bool
    {
        return $this->isGuest($user);
    }

    public function invite(?UserModel $user, ?UserModel $model = null): bool
    {
        return $this->isFullUser($user) || $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function read(?UserModel $user, ?UserModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isUser($user) && $this->isOwner($user, $model));
    }

    public function readAll(?UserModel $user, ?UserModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }

    public function resend(?UserModel $user, ?UserModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user);
    }

    public function updateRole(?UserModel $user, ?UserModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) && ($user->id !== $model->id);
    }

    public function update(?UserModel $user, ?UserModel $model = null): bool
    {
        return $this->isUser($user) && $this->isOwner($user, $model);
    }

    public function assign(?UserModel $user, ?UserModel $model = null): bool
    {
        $areColleagues = @$user->organisation_id == @$model->organisation_id;

        return $this->isFullUser($user) && $this->isFullUser($model) && $areColleagues;
    }

    public function deleteSelf(?UserModel $user, $model = null): bool
    {
        return  $user->id == $model->id;
    }
}
