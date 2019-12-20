<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Device;

class DevicePolicy extends Policy
{
    public function create(?User $user, ?Device $model = null): bool
    {
        return $this->isAdmin($user) || $this->isUser($user);
    }

    public function read(?User $user, ?Device $model = null): bool
    {
        return ($this->isAdmin($user) || $this->isUser($user)) && $this->isOwner($user, $model);
    }
    
    public function readAll(?User $user, ?Device $model = null): bool
    {
        return $this->isAdmin($user) || $this->isUser($user);
    }

    public function update(?User $user, ?Device $model = null): bool
    {
        return ($this->isAdmin($user) || $this->isUser($user)) && $this->isOwner($user, $model);
    }

    public function delete(?User $user, ?Device $model = null): bool
    {
        return ($this->isAdmin($user) || $this->isUser($user)) && $this->isOwner($user, $model);
    }
}
