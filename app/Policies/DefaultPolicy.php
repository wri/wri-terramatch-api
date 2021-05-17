<?php

namespace App\Policies;

use App\Models\User as UserModel;

class DefaultPolicy extends Policy
{
    public function yes(?UserModel $user, $model = null): bool
    {
        return true;
    }

    public function no(?UserModel $user, $model = null): bool
    {
        return false;
    }
}
