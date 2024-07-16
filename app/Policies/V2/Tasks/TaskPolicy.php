<?php

namespace App\Policies\V2\Tasks;

use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Tasks\Task;
use App\Policies\Policy;

class TaskPolicy extends Policy
{
    public function read(?User $user, ?Task $task = null): bool
    {
        if ($user->can('framework-' . $task->project->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $task)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $task)) {
            return true;
        }

        return false;
    }

    public function readAll(?User $user, ?Task $task = null): bool
    {
        return $user->hasAnyPermission(['projects-manage', 'framework-terrafund', 'framework-ppc', 'framework-hbf']);
    }

    public function update(?User $user, ?Task $task = null): bool
    {
        if ($user->can('framework-' . $task->project->framework_key)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $task)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $task);
    }

    public function submit(?User $user, ?Task $task = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $task)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $task);
    }

    public function delete(?User $user, ?Task $task = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $task)) {
            return true;
        }

        return $user->can('framework-' . $task->project->framework_key);
    }

    public function uploadFiles(?User $user, ?Task $task = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $task)) {
            return true;
        }

        return $this->isUser($user) || $this->isAdmin($user);
    }

    public function approve(?User $user, ?Task $task = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $task)) {
            return true;
        }

        return $user->can('framework-' .  $task->project->framework_key);
    }

    public function updateFileProperties(?User $user, ?Task $task = null): bool
    {
        if ($user->can('framework-' . $task->project->framework_key)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $task)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $task);
    }

    public function deleteFiles(?User $user, ?Task $task = null): bool
    {
        if ($user->can('framework-' . $task->project->framework_key)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $task)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $task);
    }

    protected function isTheirs(?User $user, ?Task $task = null): bool
    {
        return $user->organisation->id == $task->project->organisation_id || $user->projects->contains($task->project->id);
    }

    protected function isManaging(?User $user, ?Task $task = null): bool
    {
        return $task->project->managers()->where('v2_project_users.user_id', $user->id)->exists();
    }

    public function export(?User $user, ?Form $form = null, ?Task $task = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $task)) {
            return true;
        }

        return $user->can('framework-' .  $form->framework_key) or
            $user->can('manage-own') && ($user->organisation->id == $task->organisation_id || $user->projects->contains($task->project_id));
    }
}
