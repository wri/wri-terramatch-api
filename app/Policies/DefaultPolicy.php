<?php

namespace App\Policies;

use App\Models\User;

class DefaultPolicy extends Policy
{
    public function yes(?User $user, $model = null): bool
    {
        return true;
    }

    public function no(?User $user, $model = null): bool
    {
        return false;
    }
}
