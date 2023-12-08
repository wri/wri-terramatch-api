<?php

namespace App\Policies;

use App\Models\PitchDocumentVersion as PitchDocumentVersionModel;
use App\Models\User as UserModel;

class PitchDocumentVersionPolicy extends Policy
{
    public function read(?UserModel $user, ?PitchDocumentVersionModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function approve(?UserModel $user, ?PitchDocumentVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending && $this->isVisible($user, $model);
    }

    public function reject(?UserModel $user, ?PitchDocumentVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isVerifiedAdmin($user) && $isPending && $this->isVisible($user, $model);
    }

    public function delete(?UserModel $user, ?PitchDocumentVersionModel $model = null): bool
    {
        $isPending = $model->status == 'pending';

        return $this->isFullUser($user) && $this->isOwner($user, $model) && $isPending && $this->isVisible($user, $model);
    }

    public function revive(?UserModel $user, ?PitchDocumentVersionModel $model = null): bool
    {
        $isRejected = $model->status == 'rejected';

        return $this->isVerifiedAdmin($user) && $isRejected;
    }
}
