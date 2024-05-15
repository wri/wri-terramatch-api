<?php

namespace App\Policies\V2\Projects;

use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Policies\Policy;
use App\StateMachines\EntityStatusStateMachine;

class ProjectPolicy extends Policy
{
    public function read(?User $user, ?Project $project = null): bool
    {
        if ($user->can('framework-' . $project->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $project)) {
            return true;
        }

        if ($user->can('projects-read')) {
            return true;
        }

        return false;

    }

    public function readAll(?User $user, ?Project $project = null): bool
    {
        return $user->hasAnyPermission(['framework-terrafund', 'framework-ppc', 'framework-hbf']);
    }

    public function update(?User $user, ?Project $project = null): bool
    {
        if ($user->can('framework-' . $project->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $project);
    }

    public function submit(?User $user, ?Project $project = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $project);
    }

    public function create(?User $user, ?Project $project = null): bool
    {
        return $user->can('manage-own');
    }

    public function deleteFiles(?User $user, ?Project $project = null): bool
    {
        if ($user->can('framework-' . $project->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $project);
    }

    public function updateFileProperties(?User $user, ?Project $project = null): bool
    {
        if ($user->can('framework-' . $project->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $project);
    }

    public function acceptInvite(?User $user, ?Project $project = null): bool
    {
        return true;
    }

    public function createReport(?User $user, ?Project $project = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $project);
    }

    public function delete(?User $user, ?Project $project = null): bool
    {
        if ($user->can('framework-' . $project->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $project)) {
            return $project->status == EntityStatusStateMachine::STARTED;
        }

        return false;
    }

    public function uploadFiles(?User $user, ?Project $project = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }

    public function approve(?User $user, ?Project $project = null): bool
    {
        return $user->can('framework-' .  $project->framework_key);
    }

    public function createNurseries(?User $user, ?Project $project = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $project);
    }

    public function createSites(?User $user, ?Project $project = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $project);
    }

    public function inviteUser(?User $user, ?Project $project = null): bool
    {
        if ($user->can('framework-' . $project->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $project);
    }

    protected function isTheirs(?User $user, ?Project $project = null): bool
    {
        return $user->organisation->id == $project->organisation_id || $user->projects->contains($project->id);
    }

    public function export(?User $user, ?Form $form = null, ?Project $project = null): bool
    {
        return $user->can('framework-' .  $form->framework_key) or
            $user->can('manage-own') && ($user->organisation->id == $project->organisation_id || $user->projects->contains($project->id));
    }

    public function createProjectMonitoring(?User $user, ?Project $project = null): bool
    {
        return $this->isAdmin($user);
    }
}
