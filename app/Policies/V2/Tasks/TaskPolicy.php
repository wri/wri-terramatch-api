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

        return false;
    }

    public function readAll(?User $user, ?Task $task = null): bool
    {
        return $user->hasAnyPermission(['framework-terrafund', 'framework-ppc']);
    }

    public function update(?User $user, ?Task $task = null): bool
    {
        if ($user->can('framework-' . $task->project->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $task);
    }

    public function submit(?User $user, ?Task $task = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $task);
    }

    public function delete(?User $user, ?Task $task = null): bool
    {
        return $user->can('framework-' . $task->project->framework_key);
    }

    public function uploadFiles(?User $user, ?Task $task = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }

    public function approve(?User $user, ?Task $task = null): bool
    {
        return $user->can('framework-' .  $task->project->framework_key);
    }

    public function updateFileProperties(?User $user, ?Task $task = null): bool
    {
        if ($user->can('framework-' . $task->project->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $task);
    }

    public function deleteFiles(?User $user, ?Task $task = null): bool
    {
        if ($user->can('framework-' . $task->project->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $task);
    }

    protected function isTheirs(?User $user, ?Task $task = null): bool
    {
        return $user->organisation->id == $task->project->organisation_id || $user->projects->contains($task->project->id);
    }

    public function export(?User $user, ?Form $form = null, ?Task $task = null): bool
    {
        return $user->can('framework-' .  $form->framework_key) or
            $user->can('manage-own') && ($user->organisation->id == $task->organisation_id || $user->projects->contains($task->project_id));
    }
}
