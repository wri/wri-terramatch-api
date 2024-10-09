<?php

namespace App\Policies;

use App\Models\ProgrammeInvite as ProgrammeInviteModel;
use App\Models\V2\User as UserModel;

class ProgrammeInvitePolicy extends Policy
{
    public function delete(?UserModel $user, ?ProgrammeInviteModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
