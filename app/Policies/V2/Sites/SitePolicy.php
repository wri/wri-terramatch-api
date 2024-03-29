<?php

namespace App\Policies\V2\Sites;

use App\Models\User;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\Sites\Site;
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

        return false;
    }

    public function readAll(?User $user, ?Site $site = null): bool
    {
        return $user->hasAnyPermission(['framework-terrafund', 'framework-ppc']);
    }

    public function update(?User $user, ?Site $site = null): bool
    {
        if ($user->can('framework-' . $site->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    public function submit(?User $user, ?Site $site = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    public function delete(?User $user, ?Site $site = null): bool
    {
        return $user->can('framework-' . $site->framework_key) or
            $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    public function updateFileProperties(?User $user, ?Site $site = null): bool
    {
        if ($user->can('framework-' . $site->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    public function deleteFiles(?User $user, ?Site $site = null): bool
    {
        if ($user->can('framework-' . $site->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    public function approve(?User $user, ?Site $site = null): bool
    {
        return $user->can('framework-' .  $site->framework_key);
    }

    public function createReport(?User $user, ?Site $site = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    protected function isTheirs(?User $user, ?Site $site = null): bool
    {
        return $user->organisation_id == $site->project->organisation_id || $user->projects->contains($site->project_id);
    }

    public function uploadFiles(?User $user, ?Site $site = null): bool
    {
        return $user->email_address_verified_at != null;
    }

    public function export(?User $user, ?Form $form = null, ?Project $project = null): bool
    {
        return $user->can('framework-' .  $form->framework_key) or
            $user->can('manage-own') && ($user->organisation->id == $project->organisation_id || $user->projects->contains($project->id));
    }

    public function createSiteMonitoring(?User $user, ?Site $site = null): bool
    {
        return $this->isAdmin($user);
    }
}
