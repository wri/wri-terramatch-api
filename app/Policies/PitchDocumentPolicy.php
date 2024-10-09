<?php

namespace App\Policies;

use App\Models\PitchDocument as PitchDocumentModel;
use App\Models\V2\User as UserModel;

class PitchDocumentPolicy extends Policy
{
    public function create(?UserModel $user, ?PitchDocumentModel $model = null): bool
    {
        return $this->isFullUser($user);
    }

    public function read(?UserModel $user, ?PitchDocumentModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }

    public function update(?UserModel $user, ?PitchDocumentModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model);
    }

    public function delete(?UserModel $user, ?PitchDocumentModel $model = null): bool
    {
        return $this->isFullUser($user) && $this->isOwner($user, $model) && $this->isVisible($user, $model);
    }

    public function readAllBy(?UserModel $user, ?PitchDocumentModel $model = null): bool
    {
        return $this->isVerifiedAdmin($user) || ($this->isFullUser($user) && $this->isOwner($user, $model));
    }
}
