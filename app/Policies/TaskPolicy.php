<?php

namespace App\Policies;

use App\Models\User;

class TaskPolicy extends Policy
{
    public function readAll(?User $user, $model = null): bool
    {
        return $this->isVerifiedAdmin($user);
    }
}
