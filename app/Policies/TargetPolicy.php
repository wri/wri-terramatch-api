<?php

namespace App\Policies;

use App\Models\Monitoring as MonitoringModel;
use App\Models\Target as TargetModel;
use App\Models\User as UserModel;

class TargetPolicy extends Policy
{
    public function create(?UserModel $user, ?TargetModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?TargetModel $model = null): bool
    {
        return
            $this->isVerifiedAdmin($user) ||
            ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function readAll(?UserModel $user, ?TargetModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function update(?UserModel $user, ?TargetModel $model = null): bool
    {
        if ($this->isFullUser($user) && $this->isOwner($user, $model)) {
            $parent = MonitoringModel::findOrFail($model->monitoring_id);

            return $parent->stage == 'negotiating_targets';
        } else {
            return false;
        }
    }
}
