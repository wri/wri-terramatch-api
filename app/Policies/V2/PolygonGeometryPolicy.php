<?php

namespace App\Policies\V2;

use App\Models\User;
use App\Models\V2\PolygonGeometry;
use App\Policies\Policy;

class PolygonGeometryPolicy extends Policy
{
    public function delete(User $user, PolygonGeometry $polygon): bool
    {
        if (!$user->hasAnyPermission(['manage-own', 'polygons-manage'])) {
            return false;
        }

        return $this->isTheirs($user, $polygon);
    }

    public function update(User $user, PolygonGeometry $polygon): bool
    {
        if (!$user->hasAnyPermission(['manage-own', 'polygons-manage'])) {
            return false;
        }

        return $this->isTheirs($user, $polygon);
    }

    protected function isTheirs(User $user, PolygonGeometry $polygon): bool
    {
        return $user->id == $polygon->created_by;
    }
}
