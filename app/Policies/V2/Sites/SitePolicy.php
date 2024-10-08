<?php

namespace App\Policies\V2\Sites;

use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
use App\Models\V2\User;
use App\Policies\Policy;

class SitePolicy extends Policy
{
    public function read(?User $user, ?Site $site = null): bool
    {
        if ($user->can('framework-' . $site->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $site)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $site)) {
            return true;
        }

        if ($user->can('view-dashboard')) {
            return true;
        }

        return false;
    }

    public function readAll(?User $user, ?Site $site = null): bool
    {
        return $user->hasAnyPermission(['projects-manage', 'framework-terrafund', 'framework-ppc', 'framework-hbf']);
    }

    public function update(?User $user, ?Site $site = null): bool
    {
        if ($user->can('framework-' . $site->framework_key)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $site)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    public function submit(?User $user, ?Site $site = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $site)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    public function delete(?User $user, ?Site $site = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $site)) {
            return true;
        }

        return $user->can('framework-' . $site->framework_key) or
            $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    public function updateFileProperties(?User $user, ?Site $site = null): bool
    {
        if ($user->can('framework-' . $site->framework_key)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $site)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    public function deleteFiles(?User $user, ?Site $site = null): bool
    {
        if ($user->can('framework-' . $site->framework_key)) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $site)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    public function approve(?User $user, ?Site $site = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $site)) {
            return true;
        }

        return $user->can('framework-' .  $site->framework_key);
    }

    public function createReport(?User $user, ?Site $site = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $site)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    protected function isTheirs(?User $user, ?Site $site = null): bool
    {
        return $user->organisation_id == $site->project->organisation_id || $user->projects->contains($site->project_id);
    }

    protected function isManaging(?User $user, ?Site $site = null): bool
    {
        return $site->project->managers()->where('v2_project_users.user_id', $user->id)->exists();
    }

    protected function isManagingProject(User $user, Project $project): bool
    {
        return $project->managers()->where('v2_project_users.user_id', $user->id)->exists();
    }

    public function uploadFiles(?User $user, ?Site $site = null): bool
    {
        if ($user->email_address_verified_at == null) {
            return false;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $site)) {
            return true;
        }

        if ($user->can('framework-' . $site->framework_key)) {
            return true;
        }

        if ($user->can('media-manage')) {
            return true;
        }

        if ($user->can('projects-manage') && $this->isManaging($user, $site)) {
            return true;
        }

        return false;
    }

    public function uploadPolygons(?User $user, ?Site $site): bool
    {
        return $site != null && $user->can('polygons-manage');
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

    public function createSiteMonitoring(?User $user, ?Site $site = null): bool
    {
        if ($user->can('projects-manage') && $this->isManaging($user, $site)) {
            return true;
        }

        return $this->isAdmin($user);
    }
}
