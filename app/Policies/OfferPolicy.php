<?php

namespace App\Policies;

use App\Models\Offer as OfferModel;
use App\Models\V2\User as UserModel;

class OfferPolicy extends Policy
{
    public function create(?UserModel $user, ?OfferModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->hasApprovedOrganisation($user);
    }

    public function read(?UserModel $user, ?OfferModel $model = null): bool
    {
        if ($this->isVerifiedAdmin($user)) {
            return true;
        } elseif ($this->isFullUser($user)) {
            if ($this->isOwner($user, $model)) {
                return true;
            } else {
                $isVisible = ! in_array($model->visibility, ['archived', 'finished']);

                return $isVisible;
            }
        } else {
            return false;
        }
    }

    public function update(?UserModel $user, ?OfferModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model);
    }

    public function updateVisibility(?UserModel $user, ?OfferModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function search(?UserModel $user, ?OfferModel $model = null): bool
    {
        return $this->isFullUser($user);
    }
}
