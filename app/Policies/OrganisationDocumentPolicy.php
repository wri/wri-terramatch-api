<?php

namespace App\Policies;

use App\Models\OrganisationDocument as OrganisationDocumentModel;
use App\Models\User as UserModel;

class OrganisationDocumentPolicy extends Policy
{
    public function create(?UserModel $user, ?OrganisationDocumentModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?OrganisationDocumentModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function update(?UserModel $user, ?OrganisationDocumentModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function delete(?UserModel $user, ?OrganisationDocumentModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model);
    }

    public function readAllBy(?UserModel $user, ?OrganisationDocumentModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }
}
