<?php

namespace App\Policies\V2;

use App\Models\V2\FinancialReport;
use App\Models\V2\User;
use App\Policies\Policy;

class FinancialReportPolicy extends Policy
{
    public function read(?User $user, ?FinancialReport $report = null): bool
    {
        if ($this->isTheirs($user, $report) || $this->isAdmin($user)) {
            return true;
        }

        return false;
    }

    public function readAll(?User $user, ?FinancialReport $report = null): bool
    {
        if ($this->isTheirs($user, $report) || $this->isAdmin($user)) {
            return true;
        }

        return false;
    }

    public function update(?User $user, ?FinancialReport $report = null): bool
    {
        if ($this->isTheirs($user, $report) || $this->isAdmin($user)) {
            return true;
        }

        return false;
    }

    public function submit(?User $user, ?FinancialReport $report = null): bool
    {
        if ($this->isTheirs($user, $report) || $this->isAdmin($user)) {
            return true;
        }

        return false;
    }

    public function delete(?User $user, ?FinancialReport $report = null): bool
    {
        if ($this->isTheirs($user, $report) || $this->isAdmin($user)) {
            return true;
        }

        return false;
    }

    protected function isTheirs(?User $user, ?FinancialReport $report = null): bool
    {
        return $user?->organisation?->id == $report->organisation_id;
    }
}
