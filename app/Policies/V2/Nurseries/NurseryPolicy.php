<?php

namespace App\Policies\V2\Nurseries;

use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use App\Policies\Policy;

class NurseryPolicy extends Policy
{
    public function read(?User $user, ?Nursery $nursery = null): bool
    {
        if ($user->can('framework-' . $nursery->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $nursery)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $nursery)) {
            return true;
        }

        if ($this->isNewRoleUser($user)) {
            return true;
        }

        return false;
    }

    public function readAll(?User $user, ?Nursery $nursery = null): bool
    {
        return $user->hasAnyPermission(['projects-manage', 'framework-terrafund', 'framework-ppc', 'framework-hbf']);
    }

    public function update(?User $user, ?Nursery $nursery = null): bool
    {
        if ($user->can('framework-' . $nursery->framework_key)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $nursery)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $nursery);
    }

    public function submit(?User $user, ?Nursery $nursery = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $nursery)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $nursery);
    }

    public function createReport(?User $user, ?Nursery $nursery = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $nursery)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $nursery);
    }

    public function updateFileProperties(?User $user, ?Nursery $nursery = null): bool
    {
        if ($user->can('framework-' . $nursery->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $nursery)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $nursery)) {
            return true;
        }

        return false;
    }

    public function deleteFiles(?User $user, ?Nursery $nursery = null): bool
    {
        if ($user->can('framework-' . $nursery->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $nursery)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $nursery)) {
            return true;
        }

        return false;
    }

    public function delete(?User $user, ?Nursery $nursery = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $nursery)) {
            return true;
        }

        return $user->can('framework-' . $nursery->framework_key) or
            $user->can('manage-own') && $this->isTheirs($user, $nursery);
    }

    public function approve(?User $user, ?Nursery $nursery = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $nursery)) {
            return true;
        }

        return $user->can('framework-' .  $nursery->framework_key);
    }

    public function export(?User $user, ?Form $form = null, ?Project $project = null): bool
    {
        if ($user->can('projects-manage') && $this->isManagingProject($user, $project)) {
            return true;
        }

        return $user->can('framework-' .  $form->framework_key) or
            $user->can('manage-own') && ($user->organisation->id == $project->organisation_id || $user->projects->contains($project->id));
    }

    protected function isTheirs(?User $user, ?Nursery $nursery = null): bool
    {
        return $user->organisation->id == $nursery->project->organisation_id || ($user->projects && $user->projects->contains($nursery->project_id));
    }

    protected function isManaging(?User $user, ?Nursery $site = null): bool
    {
        return $site->project->managers()->where('v2_project_users.user_id', $user->id)->exists();
    }

    protected function isManagingProject(User $user, Project $project): bool
    {
        return $project->managers()->where('v2_project_users.user_id', $user->id)->exists();
    }

    public function uploadFiles(?User $user, ?Nursery $nursery = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $nursery)) {
            return true;
        }

        return $this->isUser($user) || $this->isAdmin($user);
    }
}
