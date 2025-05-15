<?php

namespace App\Policies\V2;

use App\Models\V2\FinancialIndicators;
use App\Models\V2\User;
use App\Policies\Policy;

class FinancialIndicatorsPolicy extends Policy
{
    public function uploadFiles(?User $user, ?FinancialIndicators $financialIndicator = null): bool
    {
        return $this->isUser($user) || $this->isTheirs($user, $financialIndicator);
    }

    public function deleteFiles(?User $user, ?FinancialIndicators $financialIndicator = null): bool
    {
        return $this->isUser($user) || $this->isTheirs($user, $financialIndicator);
    }

    protected function isTheirs(?User $user, ?FinancialIndicators $financialIndicator = null): bool
    {
        return $user->organisation->id == $financialIndicator->organisation_id;
    }
}