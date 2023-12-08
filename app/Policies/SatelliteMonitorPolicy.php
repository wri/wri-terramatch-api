<?php

namespace App\Policies;

use App\Models\SatelliteMonitor as SatelliteMonitorModel;
use App\Models\User as UserModel;

class SatelliteMonitorPolicy extends Policy
{
    public function create(?UserModel $user, ?SatelliteMonitorModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user);
    }

    public function readAll(?UserModel $user): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isFullUser($user) || $this->isTerrafundAdmin($user);
    }

    public function read(?UserModel $user): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isFullUser($user) || $this->isTerrafundAdmin($user);
    }
}
