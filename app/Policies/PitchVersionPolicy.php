<?php

namespace App\Policies;

use App\Models\PitchVersion as PitchVersionModel;
use App\Models\User as UserModel;

class PitchVersionPolicy extends Policy
{
    public function read(?UserModel $user, ?PitchVersionModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function approve(?UserModel $user, ?PitchVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending;
    }

    public function reject(?UserModel $user, ?PitchVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending;
    }

    public function delete(?UserModel $user, ?PitchVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isFullUser($user) && $this->isOwner($user, $model) && $isPending;
    }

    public function revive(?UserModel $user, ?PitchVersionModel $model = null): bool
    {
        $isRejected = $model->status == 'rejected';

        return $this->isVerifiedAdmin($user) && $isRejected;
    }
}
