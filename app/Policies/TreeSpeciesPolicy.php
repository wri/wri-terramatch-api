<?php

namespace App\Policies;

use App\Models\TreeSpecies as TreeSpeciesModel;
use App\Models\V2\User as UserModel;

class TreeSpeciesPolicy extends Policy
{
    public function create(?UserModel $user, ?TreeSpeciesModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?TreeSpeciesModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function update(?UserModel $user, ?TreeSpeciesModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model)) || $this->isAdmin($user);
    }

    public function delete(?UserModel $user, ?TreeSpeciesModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model)) || $this->isAdmin($user);
    }

    public function readAllBy(?UserModel $user, ?TreeSpeciesModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }
}
