<?php

namespace App\Policies;

use App\Models\User as UserModel;

class SiteRestorationMethodPolicy extends Policy
{
    public function readAll(?UserModel $user): bool
    {
        return $this->isFullUser($user);
    }
}
