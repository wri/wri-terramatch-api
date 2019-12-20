<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OfferContact;

class OfferContactPolicy extends Policy
{
    public function create(?User $user, ?OfferContact $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function delete(?User $user, ?OfferContact $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }
}
