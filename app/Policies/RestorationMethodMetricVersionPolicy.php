<?php

namespace App\Policies;

use App\Models\RestorationMethodMetricVersion as RestorationMethodMetricVersionModel;
use App\Models\User as UserModel;

class RestorationMethodMetricVersionPolicy extends Policy
{
    public function read(?UserModel $user, ?RestorationMethodMetricVersionModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function approve(?UserModel $user, ?RestorationMethodMetricVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending && $this->isVisible($user, $model);
    }

    public function reject(?UserModel $user, ?RestorationMethodMetricVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending && $this->isVisible($user, $model);
    }

    public function delete(?UserModel $user, ?RestorationMethodMetricVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isFullUser($user) && $this->isOwner($user, $model) && $isPending && $this->isVisible($user, $model);
    }

    public function revive(?UserModel $user, ?RestorationMethodMetricVersionModel $model = null): bool
    {
        $isRejected = $model->status == 'rejected';

        return $this->isVerifiedAdmin($user) && $isRejected;
    }
}
