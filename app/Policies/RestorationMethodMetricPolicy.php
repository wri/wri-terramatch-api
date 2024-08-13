<?php

namespace App\Policies;

use App\Models\RestorationMethodMetric as RestorationMethodMetricModel;
use App\Models\V2\User as UserModel;

class RestorationMethodMetricPolicy extends Policy
{
    public function create(?UserModel $user, ?RestorationMethodMetricModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?RestorationMethodMetricModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function update(?UserModel $user, ?RestorationMethodMetricModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model);
    }

    public function delete(?UserModel $user, ?RestorationMethodMetricModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model);
    }

    public function readAllBy(?UserModel $user, ?RestorationMethodMetricModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }
}
