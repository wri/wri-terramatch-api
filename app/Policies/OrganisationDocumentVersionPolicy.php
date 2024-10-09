<?php

namespace App\Policies;

use App\Models\OrganisationDocumentVersion as OrganisationDocumentVersionModel;
use App\Models\V2\User as UserModel;

class OrganisationDocumentVersionPolicy extends Policy
{
    public function read(?UserModel $user, ?OrganisationDocumentVersionModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function approve(?UserModel $user, ?OrganisationDocumentVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending;
    }

    public function reject(?UserModel $user, ?OrganisationDocumentVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending;
    }

    public function delete(?UserModel $user, ?OrganisationDocumentVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isFullUser($user) && $this->isOwner($user, $model) && $isPending;
    }

    public function revive(?UserModel $user, ?OrganisationDocumentVersionModel $model = null): bool
    {
        $isRejected = $model->status == 'rejected';

        return $this->isVerifiedAdmin($user) && $isRejected;
    }
}
