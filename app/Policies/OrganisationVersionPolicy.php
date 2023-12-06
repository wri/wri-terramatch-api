<?php

namespace App\Policies;

use App\Models\OrganisationVersion as OrganisationVersionModel;
use App\Models\User as UserModel;

class OrganisationVersionPolicy extends Policy
{
    public function read(?UserModel $user, ?OrganisationVersionModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function approve(?UserModel $user, ?OrganisationVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending;
    }

    public function reject(?UserModel $user, ?OrganisationVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending;
    }

    public function delete(?UserModel $user, ?OrganisationVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isFullUser($user) && $this->isOwner($user, $model) && $isPending;
    }

    public function revive(?UserModel $user, ?OrganisationVersionModel $model = null): bool
    {
        $isRejected = $model->status == 'rejected';

        return $this->isVerifiedAdmin($user) && $isRejected;
    }
}
