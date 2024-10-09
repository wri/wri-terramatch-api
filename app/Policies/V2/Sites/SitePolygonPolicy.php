<?php

namespace App\Policies\V2\Sites;

use App\Models\V2\Sites\Site;
use App\Models\V2\Sites\SitePolygon;
use App\Models\V2\User;
use App\Policies\Policy;

class SitePolygonPolicy extends Policy
{
    public function update(?User $user, ?SitePolygon $sitePolygon = null): bool
    {
        $site = $sitePolygon->site()->first();

        if ($user->can('framework-' . $site->framework_key)) {
            return true;
        }

        return $user->can('manage-own') && $this->isTheirs($user, $site);
    }

    protected function isTheirs(?User $user, ?Site $site = null): bool
    {
        return $user->organisation_id == $site->project->organisation_id || $user->projects->contains($site->project_id);
    }
}
