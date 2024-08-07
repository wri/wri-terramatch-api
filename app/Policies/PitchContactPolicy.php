<?php

namespace App\Policies;

use App\Models\PitchContact as PitchContactModel;
use App\Models\V2\User as UserModel;

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
