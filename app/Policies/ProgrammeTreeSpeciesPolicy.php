<?php

namespace App\Policies;

use App\Models\ProgrammeTreeSpecies as ProgrammeTreeSpeciesModel;
use App\Models\User as UserModel;

class ProgrammeTreeSpeciesPolicy extends Policy
{
    public function create(?UserModel $user): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user);
    }

    public function delete(?UserModel $user, ?ProgrammeTreeSpeciesModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user);
    }

    public function readAll(?UserModel $user, ?ProgrammeTreeSpeciesModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user);
    }
}
