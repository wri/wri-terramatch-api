<?php

namespace App\Policies;

use App\Models\Device as DeviceModel;
use App\Models\User as UserModel;

class DevicePolicy extends Policy
{
    public function create(?UserModel $user, ?DeviceModel $model = null): bool
    {
        return $this->isAdmin($user) || $this->isUser($user);
    }

    public function read(?UserModel $user, ?DeviceModel $model = null): bool
    {
        return ($this->isAdmin($user) || $this->isUser($user)) && $this->isOwner($user, $model);
    }

    public function readAll(?UserModel $user, ?DeviceModel $model = null): bool
    {
        return $this->isAdmin($user) || $this->isUser($user);
    }

    public function update(?UserModel $user, ?DeviceModel $model = null): bool
    {
        return ($this->isAdmin($user) || $this->isUser($user)) && $this->isOwner($user, $model);
    }

    public function delete(?UserModel $user, ?DeviceModel $model = null): bool
    {
        return ($this->isAdmin($user) || $this->isUser($user)) && $this->isOwner($user, $model);
    }
}
