<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PitchContact;

class PitchContactPolicy extends Policy
{
    public function create(?User $user, ?PitchContact $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function delete(?User $user, ?PitchContact $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }
}
