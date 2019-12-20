<?php

namespace App\Policies;

use App\Models\User;
use App\Models\TreeSpecies;

class TreeSpeciesPolicy extends Policy
{
    public function create(?User $user, ?TreeSpecies $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?User $user, ?TreeSpecies $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function update(?User $user, ?TreeSpecies $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }

    public function delete(?User $user, ?TreeSpecies $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }
}
