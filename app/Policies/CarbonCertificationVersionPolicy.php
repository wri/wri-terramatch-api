<?php

namespace App\Policies;

use App\Models\CarbonCertificationVersion as CarbonCertificationVersionModel;
use App\Models\V2\User as UserModel;

class CarbonCertificationVersionPolicy extends Policy
{
    public function read(?UserModel $user, ?CarbonCertificationVersionModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function approve(?UserModel $user, ?CarbonCertificationVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending && $this->isVisible($user, $model);
    }

    public function reject(?UserModel $user, ?CarbonCertificationVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending && $this->isVisible($user, $model);
    }

    public function delete(?UserModel $user, ?CarbonCertificationVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isFullUser($user) && $this->isOwner($user, $model) && $isPending && $this->isVisible($user, $model);
    }

    public function revive(?UserModel $user, ?CarbonCertificationVersionModel $model = null): bool
    {
        $isRejected = $model->status == 'rejected';

        return $this->isVerifiedAdmin($user) && $isRejected;
    }
}
