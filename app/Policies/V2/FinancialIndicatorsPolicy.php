<?php

namespace App\Policies\V2\Projects;

use App\Models\V2\FinancialIndicators;
use App\Models\V2\User;
use App\Policies\Policy;

class FinancialIndicatorsPolicy extends Policy
{
    public function uploadFiles(?User $user, ?FinancialIndicators $financialIndicator = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $financialIndicator)) {
            return true;
        }

        return $this->isUser($user) || $this->isAdmin($user);
    }
}
