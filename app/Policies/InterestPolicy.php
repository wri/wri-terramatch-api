<?php

namespace App\Policies;

use App\Models\Interest as InterestModel;
use App\Models\V2\User as UserModel;

class InterestPolicy extends Policy
{
    public function create(?UserModel $user, ?InterestModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function delete(?UserModel $user, ?InterestModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function readAll(?UserModel $user, ?InterestModel $model = null): bool
    {
        return $this->isFullUser($user);
    }
}
