<?php

namespace App\Policies\V2;

use App\Models\V2\FinancialIndicators;
use App\Models\V2\User;
use App\Policies\Policy;

class FinancialIndicatorsPolicy extends Policy
{
    public function uploadFiles(?User $user, ?FinancialIndicators $financialIndicator = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
            return true;
        }

        return $this->isUser($user) || $this->isAdmin($user);
    }

    public function deleteFiles(?User $user, ?FinancialIndicators $financialIndicator = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
            return true;
        }

        return $this->isUser($user) || $this->isAdmin($user);
    }
}
