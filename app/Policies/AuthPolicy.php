<?php

namespace App\Policies;

use App\Models\User as UserModel;

class AuthPolicy extends Policy
{
    public function login(?UserModel $user, $model = null): bool
    {
        return $this->isGuest($user);
    }

    public function logout(?UserModel $user, $model = null): bool
    {
        return ! $this->isGuest($user);
    }

    public function refresh(?UserModel $user, $model = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }

    public function resendByEmail(?UserModel $user, $model = null): bool
    {
        return $this->isAdmin($user);
    }

    public function verify(?UserModel $user, $model = null): bool
    {
        $isUnverifiedUser = $this->isUser($user) && ! $this->isVerifiedUser($user);
        $isUnverifiedAdmin = $this->isAdmin($user) && ! $this->isVerifiedAdmin($user);

        return $isUnverifiedUser || $isUnverifiedAdmin;
    }

    public function resend(?UserModel $user, $model = null): bool
    {
        $isUnverifiedUser = $this->isUser($user) && ! $this->isVerifiedUser($user);
        $isUnverifiedAdmin = $this->isAdmin($user) && ! $this->isVerifiedAdmin($user);

        return $isUnverifiedUser || $isUnverifiedAdmin;
    }

    public function reset(?UserModel $user, $model = null): bool
    {
        return $this->isGuest($user) || $this->isAdmin($user);
    }

    public function change(?UserModel $user, $model = null): bool
    {
        return $this->isGuest($user);
    }

    public function me(?UserModel $user, $model = null): bool
    {
        return $this->isUser($user) ||
            $this->isAdmin($user) ||
            $this->isTerrafundAdmin($user) ||
            $this->isServiceAccount($user) ||
            $this->isNewRoleUser($user);
    }
}
