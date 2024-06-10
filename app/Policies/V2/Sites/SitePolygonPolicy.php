<?php

namespace App\Policies\V2\Sites;

use App\Models\User;
use App\Models\V2\Sites\SitePolygon;
use App\Policies\Policy;

class SitePolygonPolicy extends Policy
{
    public function update(?User $user, ?SitePolygon $sitePolygon = null): bool
    {
        return true;
    }
}
