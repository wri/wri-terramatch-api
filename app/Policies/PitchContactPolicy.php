<?php

namespace App\Policies;

use App\Models\User as UserModel;
use App\Models\PitchContact as PitchContactModel;

class PitchContactPolicy extends Policy
{
    public function create(?UserModel $user, ?PitchContactModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function delete(?UserModel $user, ?PitchContactModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model);
    }
}
