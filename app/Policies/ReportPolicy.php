<?php

namespace App\Policies;

use App\Models\User as UserModel;

class ReportPolicy extends Policy
{
    public function readAll(?UserModel $user, $model = null): bool
    {
        return $this->isVerifiedAdmin($user);
    }
}
