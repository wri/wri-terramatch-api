<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OfferDocument;

class OfferDocumentPolicy extends Policy
{
    public function create(?User $user, ?OfferDocument $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?User $user, ?OfferDocument $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function update(?User $user, ?OfferDocument $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }

    public function delete(?User $user, ?OfferDocument $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && !$this->isCompleted($user, $model);
    }
}
