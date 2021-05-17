<?php

namespace App\Policies;

use App\Models\User as UserModel;
use App\Models\Match as MatchModel;

class MatchPolicy extends Policy
{
    public function readAll(?UserModel $user, ?MatchModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?MatchModel $model = null): bool
    {
        return
            $this->isVerifiedAdmin($user) ||
            ($this->isFullUser($user) && $this->isOwner($user, $model));
    }
}
