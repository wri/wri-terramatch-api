<?php

namespace App\Policies;

use App\Models\CarbonCertification as CarbonCertificationModel;
use App\Models\V2\User as UserModel;

class CarbonCertificationPolicy extends Policy
{
    public function create(?UserModel $user, ?CarbonCertificationModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?CarbonCertificationModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function update(?UserModel $user, ?CarbonCertificationModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model);
    }

    public function delete(?UserModel $user, ?CarbonCertificationModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model);
    }

    public function readAllBy(?UserModel $user, ?CarbonCertificationModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }
}
