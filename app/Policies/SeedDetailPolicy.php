<?php

namespace App\Policies;

use App\Models\SeedDetail as SeedDetailModel;
use App\Models\User as UserModel;

class SeedDetailPolicy extends Policy
{
    public function delete(?UserModel $user, ?SeedDetailModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user);
    }
}
