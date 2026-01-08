<?php

namespace App\Policies\V2;

use App\Models\V2\Action;
use App\Models\V2\User;
use App\Policies\Policy;

class ActionPolicy extends Policy
{
    public function read(?User $user, ?Action $action = null): bool
    {
        if ($user->can('manage-own') && $this->isTheirs($user, $action)) {
            return true;
        }

        return false;
    }

    protected function isTheirs(?User $user, ?Action $action = null): bool
    {
        if ($user->all_my_organisations->pluck('id')->contains($action->organisation_id)) {
            return true;
        }

        if (! empty($action->project_id) && $user->projects->pluck('id')->contains($action->project_id)) {
            return true;
        }

        return false;
    }
}
