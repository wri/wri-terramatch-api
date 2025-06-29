<?php

namespace App\Policies\V2\UpdateRequests;

use App\Models\V2\Projects\Project;
use App\Models\V2\UpdateRequests\UpdateRequest;
use App\Models\V2\User;
use App\Policies\Policy;

class UpdateRequestPolicy extends Policy
{
    public function create(?User $user, ?UpdateRequest $updateRequest = null): bool
    {
        if ($user->can('manage-own') && $this->isTheirs($user, $updateRequest->project)) {
            return true;
        }

        return false;
    }

    public function read(?User $user, ?UpdateRequest $updateRequest = null): bool
    {
        if ($user->can('manage-own') && $this->isTheirs($user, $updateRequest->project)) {
            return true;
        }

        return $user->can('framework-' . $updateRequest->framework_key);
    }

    public function readAll(?User $user, ?UpdateRequest $updateRequest = null): bool
    {
        return $user->hasAnyPermission(['framework-terrafund', 'framework-ppc', 'framework-hbf']);
    }

    public function update(?User $user, ?UpdateRequest $updateRequest = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $updateRequest);
    }

    public function delete(?User $user, ?UpdateRequest $updateRequest = null): bool
    {
        if ($user->can('manage-own') && $this->isTheirs($user, $updateRequest->project)) {
            return true;
        }

        return $user->can('framework-' . $updateRequest->framework_key);
    }

    public function approve(?User $user, ?UpdateRequest $updateRequest = null): bool
    {
        if ($user->hasRole('project-manager')) {
            return $this->projectManagerCan($user, $updateRequest);
        }

        return $user->can('framework-' .  $updateRequest->framework_key);
    }

    public function reject(?User $user, ?UpdateRequest $updateRequest = null): bool
    {
        if ($user->hasRole('project-manager')) {
            return $this->projectManagerCan($user, $updateRequest);
        }

        return $user->can('framework-' .  $updateRequest->framework_key);
    }

    public function moreinfo(?User $user, ?UpdateRequest $updateRequest = null): bool
    {
        if ($user->hasRole('project-manager')) {
            return $this->projectManagerCan($user, $updateRequest);
        }

        return $user->can('framework-' .  $updateRequest->framework_key);
    }

    public function projectManagerCan(?User $user, ?UpdateRequest $updateRequest = null): bool
    {
        $frameworkKeys = $user->projectsFrameworkKey();

        return $frameworkKeys->contains($updateRequest->framework_key);
    }

    protected function isTheirs(?User $user, ?Project $project = null): bool
    {
        return $user->organisation_id == $project->organisation_id || ($user->projects && $user->projects->contains($project->id));
    }
}
