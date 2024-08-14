<?php

namespace App\Policies;

use App\Models\ProgressUpdate as ProgressUpdateModel;
use App\Models\V2\User as UserModel;

class ProgressUpdatePolicy extends Policy
{
    public function create(?UserModel $user, ?ProgressUpdateModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?ProgressUpdateModel $model = null): bool
    {
        return
            $this->isVerifiedAdmin($user) ||
            ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function readAll(?UserModel $user, ?ProgressUpdateModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $this->isFullUser($user);
    }
}
