<?php

namespace App\Policies\V2\Nurseries;

use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use App\Policies\Policy;

class NurseryReportPolicy extends Policy
{
    public function read(?User $user, ?NurseryReport $report = null): bool
    {
        if ($user->can('framework-' . $report->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $report)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
            return true;
        }

        return false;
    }

    public function readAll(?User $user, ?NurseryReport $report = null): bool
    {
        return $user->hasAnyPermission(['projects-manage', 'framework-terrafund', 'framework-ppc', 'framework-hbf']);
    }

    public function update(?User $user, ?NurseryReport $report = null): bool
    {
        if ($user->can('framework-' . $report->framework_key)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $report);
    }

    public function submit(?User $user, ?NurseryReport $report = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $report);
    }

    public function delete(?User $user, ?NurseryReport $report = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
            return true;
        }

        return $user->can('framework-' . $report->framework_key);
    }

    public function updateFileProperties(?User $user, ?NurseryReport $report = null): bool
    {
        if ($user->can('framework-' . $report->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $report)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
            return true;
        }

        return false;
    }

    public function deleteFiles(?User $user, ?NurseryReport $report = null): bool
    {
        if ($user->can('framework-' . $report->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $report)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
            return true;
        }

        return false;
    }

    public function uploadFiles(?User $user, ?NurseryReport $report = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
            return true;
        }

        return $this->isUser($user) || $this->isAdmin($user);
    }

    public function approve(?User $user, ?NurseryReport $report = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $report)) {
            return true;
        }

        return $user->can('framework-' .  $report->framework_key);
    }

    protected function isTheirs(?User $user, ?NurseryReport $report = null): bool
    {
        return $user->organisation->id == $report->nursery->project->organisation_id || ($user->projects && $user->projects->contains($report->project->id));
    }

    protected function isManaging(?User $user, ?NurseryReport $report = null): bool
    {
        return $report->project->managers()->where('v2_project_users.user_id', $user->id)->exists();
    }

    protected function isManagingProject(User $user, Project $project): bool
    {
        return $project->managers()->where('v2_project_users.user_id', $user->id)->exists();
    }

    public function export(?User $user, ?Form $form = null, ?Project $project = null): bool
    {
        if ($user->primaryRole?->name == 'project-manager') {
            return $user->my_frameworks_slug->contains($form->framework_key);
        }

        if ($user->can('projects-manage') && $this->isManagingProject($user, $project)) {
            return true;
        }

        return $user->can('framework-' .  $form->framework_key) or
            $user->can('manage-own') && ($user->organisation->id == $project->organisation_id || $user->projects->contains($project->id));
    }
}
