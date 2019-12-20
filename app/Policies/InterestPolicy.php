<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Interest;

class InterestPolicy extends Policy
{
    public function create(?User $user, ?Interest $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function delete(?User $user, ?Interest $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function readAll(?User $user, ?Interest $model = null): bool
    {
        return $this->isFullUser($user);
    }
}
