<?php

namespace App\Policies;

use App\Models\User as UserModel;

class FrameworkPolicy extends Policy
{
    public function update(?UserModel $user): bool
    {
        return $user->hasAnyPermission(['framework-terrafund', 'framework-ppc']);
    }
}
