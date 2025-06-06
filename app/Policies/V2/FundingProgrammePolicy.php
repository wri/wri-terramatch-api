<?php

namespace App\Policies\V2;

use App\Models\V2\FundingProgramme;
use App\Models\V2\User;
use App\Policies\Policy;

class FundingProgrammePolicy extends Policy
{
    public function uploadFiles(?User $user, ?FundingProgramme $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || $user->hasAnyPermission(['framework-terrafund', 'framework-ppc', 'framework-hbf']);
    }
}
