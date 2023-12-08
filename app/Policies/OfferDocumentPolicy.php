<?php

namespace App\Policies;

use App\Models\OfferDocument as OfferDocumentModel;
use App\Models\User as UserModel;

class OfferDocumentPolicy extends Policy
{
    public function create(?UserModel $user, ?OfferDocumentModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?OfferDocumentModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function update(?UserModel $user, ?OfferDocumentModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model);
    }

    public function delete(?UserModel $user, ?OfferDocumentModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model);
    }
}
