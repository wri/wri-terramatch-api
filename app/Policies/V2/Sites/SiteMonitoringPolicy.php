<?php

namespace App\Policies\V2\Sites;

use App\Models\V2\User;
use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SiteMonitoring;
use App\Policies\Policy;

class SiteMonitoringPolicy extends Policy
{
    public function read(?User $user, ?SiteMonitoring $siteMonitoring = null): bool
    {
        if ($user->can('framework-' . $siteMonitoring->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $siteMonitoring->site)) {
            return true;
        }

        return false;
    }

    public function update(?User $user, ?SiteMonitoring $siteMonitoring = null): bool
    {
        return $user->can('manage-own') && $this->isTheirs($user, $siteMonitoring->site);
    }

    public function delete(?User $user, ?SiteMonitoring $siteMonitoring = null): bool
    {
        if ($user->can('framework-' . $siteMonitoring->framework_key)) {
            return true;
        }

        if ($user->can('manage-own') && $this->isTheirs($user, $siteMonitoring->site)) {
            return true;
        }

        return false;
    }

    public function updateFileProperties(?User $user, ?SiteMonitoring $siteMonitoring = null): bool
    {
        if ($user->can('framework-' . $siteMonitoring->framework_key)) {
            return true;
        }
        return $user->can('manage-own') && $this->isTheirs($user, $siteMonitoring->site);
    }

    public function uploadFiles(?User $user, ?SiteMonitoring $siteMonitoring = null): bool
    {
        return $user->email_address_verified_at != null;
    }

    protected function isTheirs(?User $user, ?Site $site = null): bool
    {
        return $user->organisation->id == $site->project->organisation_id || $user->projects->contains($site->project_id);
    }

}
