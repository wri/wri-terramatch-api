<?php

namespace App\Policies\V2\Nurseries;

use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Nurseries\Nursery;
use App\Models\V2\Projects\Project;
use App\Policies\Policy;

class NurseryPolicy extends Policy
{
    public function read(?User $user, ?Nursery $nursey = null): bool
    {
        if ($user->can('framework-' . $nursey->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $nursey)) {
            return true;
        }

        return false;
    }

    public function readAll(?User $user, ?Nursery $nursey = null): bool
    {
        return $user->hasAnyPermission(['framework-terrafund', 'framework-ppc']);
    }

    public function update(?User $user, ?Nursery $nursey = null): bool
    {
        if ($user->can('framework-' . $nursey->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $nursey);
    }

    public function submit(?User $user, ?Nursery $nursey = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $nursey);
    }

    public function createReport(?User $user, ?Nursery $nursey = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $nursey);
    }

    public function updateFileProperties(?User $user, ?Nursery $nursey = null): bool
    {
        if ($user->can('framework-' . $nursey->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $nursey)) {
            return true;
        }

        return false;
    }

    public function deleteFiles(?User $user, ?Nursery $nursey = null): bool
    {
        if ($user->can('framework-' . $nursey->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $nursey)) {
            return true;
        }

        return false;
    }

    public function delete(?User $user, ?Nursery $nursey = null): bool
    {
        return $user->can('framework-' . $nursey->framework_key) or
            $user->can('manage-own') && $this->isTheirs($user, $nursey);
    }

    public function approve(?User $user, ?Nursery $nursey = null): bool
    {
        return $user->can('framework-' .  $nursey->framework_key);
    }

    public function export(?User $user, ?Form $form = null, ?Project $project = null): bool
    {
        return $user->can('framework-' .  $form->framework_key) or
            $user->can('manage-own') && ($user->organisation->id == $project->organisation_id || $user->projects->contains($project->id));
    }

    protected function isTheirs(?User $user, ?Nursery $nursey = null): bool
    {
        return $user->organisation->id == $nursey->project->organisation_id || ($user->projects && $user->projects->contains($nursey->project_id));
    }

    public function uploadFiles(?User $user, ?Nursery $nursery = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }
}
