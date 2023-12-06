<?php

namespace App\Policies;

use App\Models\SatelliteMap as SatelliteMapModel;
use App\Models\User as UserModel;

class SatelliteMapPolicy extends Policy
{
    public function create(?UserModel $user, ?SatelliteMapModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user);
    }

    public function readAll(?UserModel $user, ?SatelliteMapModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?SatelliteMapModel $model = null): bool
    {
        return
            $this->isVerifiedAdmin($user) ||
            ($this->isFullUser($user) && $this->isOwner($user, $model));
    }
}
