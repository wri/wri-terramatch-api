<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Match;

class MatchPolicy extends Policy
{
    public function readAll(?User $user, ?Match $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?User $user, ?Match $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
