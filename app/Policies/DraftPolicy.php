<?php

namespace App\Policies;

use App\Models\User as UserModel;
use App\Models\Draft as DraftModel;

class DraftPolicy extends Policy
{
    public function create(?UserModel $user, ?DraftModel $model = null): bool
    {
        return $this->isVerifiedUser($user);
    }

    public function read(?UserModel $user, ?DraftModel $model = null): bool
    {
        return $this->isVerifiedUser($user) && $this->isOwner($user, $model);
    }

    public function readAll(?UserModel $user, ?DraftModel $model = null): bool
    {
        return $this->isVerifiedUser($user);
    }

    public function update(?UserModel $user, ?DraftModel $model = null): bool
    {
        return $this->isVerifiedUser($user) && $this->isOwner($user, $model);
    }

    public function delete(?UserModel $user, ?DraftModel $model = null): bool
    {
        return $this->isVerifiedUser($user) && $this->isOwner($user, $model);
    }

    public function publish(?UserModel $user, ?DraftModel $model = null): bool
    {
        return $this->isVerifiedUser($user) && $this->isOwner($user, $model);
    }
}
