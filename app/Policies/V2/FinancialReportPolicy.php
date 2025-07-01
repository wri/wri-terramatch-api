<?php

namespace App\Policies\V2;

use App\Models\V2\Forms\Form;
use App\Models\V2\User;
use App\Models\V2\FinancialReport;
use App\Policies\Policy;

class FinancialReportPolicy extends Policy
{
    public function read(?User $user, ?FinancialReport $report = null): bool
    {
        return true;
        // if ($user->can('framework-' . $report->framework_key)) {
        //     return true;
        // }

        // if ($user->can('manage-own') && $this->isTheirs($user, $report)) {
        //     return true;
        // }

        // if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
        //     return true;
        // }

        // if ($user->can('view-dashboard')) {
        //     return true;
        // }

        // return false;
    }

    public function readAll(?User $user, ?FinancialReport $report = null): bool
    {
        return true;
        // return $user->hasAnyPermission(['projects-manage', 'framework-terrafund', 'framework-ppc', 'framework-hbf']);
    }

    public function update(?User $user, ?FinancialReport $report = null): bool
    {
        return true;
        // if ($user->can('manage-own') && $this->isTheirs($user, $report)) {
        //     return true;
        // }

        // return $this->isTheirs($user, $report);
    }

    public function submit(?User $user, ?FinancialReport $report = null): bool
    {
        return true;
        // if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
        //     return true;
        // }

        // return $user->can('manage-own') && $this->isTheirs($user, $report);
    }

    public function delete(?User $user, ?FinancialReport $report = null): bool
    {
        return true;
        // if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
        //     return true;
        // }

        // return $user->can('framework-' . $report->framework_key);
    }

    protected function isTheirs(?User $user, ?FinancialReport $report = null): bool
    {
        return $user->organisation->id == $report->organisation_id;
    }
} 