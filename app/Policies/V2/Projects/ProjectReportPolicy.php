<?php

namespace App\Policies\V2\Projects;

use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Policies\Policy;

class ProjectReportPolicy extends Policy
{
    public function read(?User $user, ?ProjectReport $report = null): bool
    {
        if ($user->can('framework-' . $report->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $report)) {
            return true;
        }

        if ($this->isNewRoleUser($user)) {
            return true;
        }

        return false;
    }

    public function readAll(?User $user, ?ProjectReport $report = null): bool
    {
        return $user->hasAnyPermission(['framework-terrafund', 'framework-ppc', 'framework-hbf']);
    }

    public function update(?User $user, ?ProjectReport $report = null): bool
    {
        if ($user->can('framework-' . $report->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $report);
    }

    public function submit(?User $user, ?ProjectReport $report = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $report);
    }

    public function delete(?User $user, ?ProjectReport $report = null): bool
    {
        return $user->can('framework-' . $report->framework_key);
    }

    public function uploadFiles(?User $user, ?ProjectReport $report = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }

    public function approve(?User $user, ?ProjectReport $report = null): bool
    {
        return $user->can('framework-' .  $report->framework_key);
    }

    public function updateFileProperties(?User $user, ?ProjectReport $report = null): bool
    {
        if ($user->can('framework-' . $report->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $report);
    }

    public function deleteFiles(?User $user, ?ProjectReport $report = null): bool
    {
        if ($user->can('framework-' . $report->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $report);
    }

    protected function isTheirs(?User $user, ?ProjectReport $report = null): bool
    {
        return $user->organisation->id == $report->project->organisation_id || $user->projects->contains($report->project->id);
    }

    public function export(?User $user, ?Form $form = null, ?Project $project = null): bool
    {
        return $user->can('framework-' .  $form->framework_key) or
            $user->can('manage-own') && ($user->organisation->id == $project->organisation_id || $user->projects->contains($project->id));
    }
}
