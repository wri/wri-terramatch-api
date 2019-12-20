<?php

namespace App\Policies;

use App\Models\User;
use App\Models\OrganisationDocument;

class OrganisationDocumentPolicy extends Policy
{
    public function create(?User $user, ?OrganisationDocument $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?User $user, ?OrganisationDocument $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function update(?User $user, ?OrganisationDocument $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function delete(?User $user, ?OrganisationDocument $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }
}
