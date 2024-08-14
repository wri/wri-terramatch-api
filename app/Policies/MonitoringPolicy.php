<?php

namespace App\Policies;

use App\Models\Monitoring as MonitoringModel;
use App\Models\V2\User as UserModel;

class MonitoringPolicy extends Policy
{
    public function create(?UserModel $user, ?MonitoringModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?MonitoringModel $model = null): bool
    {
        return
            $this->isVerifiedAdmin($user) ||
            ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function readLandGeoJson(?UserModel $user, ?MonitoringModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user);
    }

    public function readAll(?UserModel $user, ?MonitoringModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function summarise(?UserModel $user, ?MonitoringModel $model = null): bool
    {
        return
            $this->isVerifiedAdmin($user) ||
            ($this->isFullUser($user) && $this->isOwner($user, $model));
    }
}
