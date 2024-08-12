<?php

namespace App\Policies;

use App\Models\OfferContact as OfferContactModel;
use App\Models\V2\User as UserModel;

class OfferContactPolicy extends Policy
{
    public function create(?UserModel $user, ?OfferContactModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function delete(?UserModel $user, ?OfferContactModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model);
    }
}
