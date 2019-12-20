<?php

namespace App\Policies;

use App\Models\User;
use App\Models\RestorationMethodMetricVersion;

class RestorationMethodMetricVersionPolicy extends Policy
{
    public function read(?User $user, ?RestorationMethodMetricVersion $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function approve(?User $user, ?RestorationMethodMetricVersion $model = null): bool
    {
        $isPending = $model->status == "pending";
        return $this->isVerifiedAdmin($user) && $isPending && !$this->isCompleted($user, $model);
    }

    public function reject(?User $user, ?RestorationMethodMetricVersion $model = null): bool
    {
        $isPending = $model->status == "pending";
        return $this->isVerifiedAdmin($user) && $isPending && !$this->isCompleted($user, $model);
    }

    public function delete(?User $user, ?RestorationMethodMetricVersion $model = null): bool
    {
        $isPending = $model->status == "pending";
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $isPending && !$this->isCompleted($user, $model);
    }
}
