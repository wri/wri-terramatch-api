<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RestorationMethodMetric;

class RestorationMethodMetricPolicy extends Policy
{
    public function create(?User $user, ?RestorationMethodMetric $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?User $user, ?RestorationMethodMetric $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function update(?User $user, ?RestorationMethodMetric $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }

    public function delete(?User $user, ?RestorationMethodMetric $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }

}
