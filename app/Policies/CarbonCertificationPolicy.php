<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CarbonCertification;

class CarbonCertificationPolicy extends Policy
{
    public function create(?User $user, ?CarbonCertification $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?User $user, ?CarbonCertification $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function update(?User $user, ?CarbonCertification $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }

    public function delete(?User $user, ?CarbonCertification $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }
}
