<?php

namespace App\Policies;

use App\Models\V2\User as UserModel;

class FrameworkInviteCodePolicy extends Policy
{
    public function read(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }

    public function readAll(?UserModel $user): bool
    {
        return $this->isVerifiedAdmin($user);
    }

    public function create(?UserModel $user): bool
    {
        return $this->isVerifiedAdmin($user);
    }

    public function delete(?UserModel $user): bool
    {
        return $this->isVerifiedAdmin($user);
    }
}
