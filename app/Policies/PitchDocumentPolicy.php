<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PitchDocument;

class PitchDocumentPolicy extends Policy
{
    public function create(?User $user, ?PitchDocument $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?User $user, ?PitchDocument $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function update(?User $user, ?PitchDocument $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }

    public function delete(?User $user, ?PitchDocument $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }
}
