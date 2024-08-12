<?php

namespace App\Policies;

use App\Models\V2\User as UserModel;

class FrameworkPolicy extends Policy
{
    public function update(?UserModel $user): bool
    {
        return $user->hasAnyPermission(['framework-terrafund', 'framework-ppc', 'framework-hbf']);
    }
}
