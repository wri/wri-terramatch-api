<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Offer;

class OfferPolicy extends Policy
{
    public function create(?User $user, ?Offer $model = null): bool
    {
        return $this->isFullUser($user) && $this->hasApprovedOrganisation($user);
    }

    public function read(?User $user, ?Offer $model = null): bool
    {
        return $this->isAdmin($user) || $this->isFullUser($user);
    }

    public function update(?User $user, ?Offer $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }

    public function search(?User $user, ?Offer $model = null): bool
    {
        return $this->isFullUser($user);
    }
}
