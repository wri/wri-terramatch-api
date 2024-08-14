<?php

namespace App\Policies;

use App\Models\Matched as MatchModel;
use App\Models\V2\User as UserModel;

class MatchedPolicy extends Policy
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
