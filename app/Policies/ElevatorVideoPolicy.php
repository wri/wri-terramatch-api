<?php

namespace App\Policies;

use App\Models\User as UserModel;
use App\Models\ElevatorVideo as ElevatorVideoModel;

class ElevatorVideoPolicy extends Policy
{
    public function create(?UserModel $user, ?ElevatorVideoModel $model = null): bool
    {
        return $this->isVerifiedUser($user);
    }

    public function read(?UserModel $user, ?ElevatorVideoModel $model = null): bool
    {
        return $this->isVerifiedUser($user) && $this->isOwner($user, $model);
    }
}
