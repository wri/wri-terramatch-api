<?php

namespace App\Policies;

use App\Models\V2\User as UserModel;

class TaskPolicy extends Policy
{
    public function readAll(?UserModel $user, $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isTerrafundAdmin($user);
    }
}
