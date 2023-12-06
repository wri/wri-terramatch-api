<?php

namespace App\Policies\V2;

use App\Models\User;
use App\Models\V2\Action;
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
        return $user->organisation->id == $action->organisation_id;
    }
}
