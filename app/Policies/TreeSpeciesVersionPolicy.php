<?php

namespace App\Policies;

use App\Models\TreeSpeciesVersion as TreeSpeciesVersionModel;
use App\Models\V2\User as UserModel;

class TreeSpeciesVersionPolicy extends Policy
{
    public function read(?UserModel $user, ?TreeSpeciesVersionModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function approve(?UserModel $user, ?TreeSpeciesVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending && $this->isVisible($user, $model);
    }

    public function reject(?UserModel $user, ?TreeSpeciesVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending && $this->isVisible($user, $model);
    }

    public function delete(?UserModel $user, ?TreeSpeciesVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isFullUser($user) && $this->isOwner($user, $model) && $isPending && $this->isVisible($user, $model);
    }

    public function revive(?UserModel $user, ?TreeSpeciesVersionModel $model = null): bool
    {
        $isRejected = $model->status == 'rejected';

        return $this->isVerifiedAdmin($user) && $isRejected;
    }
}
