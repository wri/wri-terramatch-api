<?php

namespace App\Policies;

use App\Models\Invasive as InvasiveModel;
use App\Models\V2\User as UserModel;

class InvasivePolicy extends Policy
{
    public function delete(?UserModel $user, ?InvasiveModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user);
    }
}
