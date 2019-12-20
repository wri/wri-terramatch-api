<?php

namespace App\Policies;

use App\Models\User;

class AuthPolicy extends Policy
{
    public function login(?User $user, $model = null): bool
    {
        return $this->isGuest($user);
    }

    public function logout(?User $user, $model = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }

    public function refresh(?User $user, $model = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }

    public function verify(?User $user, $model = null): bool
    {
        $isUnverifiedUser = $this->isUser($user) && !$this->isVerifiedUser($user);
        $isUnverifiedAdmin = $this->isAdmin($user) && !$this->isVerifiedAdmin($user);
        return $isUnverifiedUser || $isUnverifiedAdmin;
    }

    public function resend(?User $user, $model = null): bool
    {
        $isUnverifiedUser = $this->isUser($user) && !$this->isVerifiedUser($user);
        $isUnverifiedAdmin = $this->isAdmin($user) && !$this->isVerifiedAdmin($user);
        return $isUnverifiedUser || $isUnverifiedAdmin;
    }

    public function reset(?User $user, $model = null): bool
    {
        return $this->isGuest($user);
    }

    public function change(?User $user, $model = null): bool
    {
        return $this->isGuest($user);
    }

    public function me(?User $user, $model = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }
}
