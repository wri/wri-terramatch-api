<?php

namespace App\Policies\V2\Projects;

use App\Models\User;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectMonitoring;
use App\Policies\Policy;

class ProjectMonitoringPolicy extends Policy
{
    public function read(?User $user, ?ProjectMonitoring $projectMonitoring = null): bool
    {
        if ($user->can('framework-' . $projectMonitoring->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $projectMonitoring)) {
            return true;
        }

        return false;
    }

    public function update(?User $user, ?ProjectMonitoring $projectMonitoring = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $projectMonitoring->project);
    }

    public function delete(?User $user, ?ProjectMonitoring $projectMonitoring = null): bool
    {
        if ($user->can('framework-' . $projectMonitoring->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $projectMonitoring->project)) {
            return true;
        }

        return false;
    }

    public function updateFileProperties(?User $user, ?ProjectMonitoring $projectMonitoring = null): bool
    {
        if ($user->can('framework-' . $projectMonitoring->framework_key)) {
            return true;
        }

        return $this->isUser($user) || $this->isAdmin($user);
    }

    public function uploadFiles(?User $user, ?ProjectMonitoring $projectMonitoring = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }

    protected function isTheirs(?User $user, ?Project $project = null): bool
    {
        return $user->organisation->id == $project->organisation_id || $user->projects->contains($project->id);
    }
}
