<?php

namespace App\Policies;

use App\Models\V2\User as UserModel;

class SocioeconomicBenefitPolicy extends Policy
{
    public function upload(?UserModel $user): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user);
    }

    public function download(?UserModel $user): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user);
    }
}
