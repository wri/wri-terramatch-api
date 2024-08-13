<?php

namespace App\Policies;

use App\Models\MediaUpload as MediaUploadModel;
use App\Models\V2\User as UserModel;

class MediaUploadPolicy extends Policy
{
    public function create(?UserModel $user): bool
    {
        return $this->isFullUser($user) || $this->isAdmin($user);
    }

    public function delete(?UserModel $user, ?MediaUploadModel $model = null): bool
    {
        return ($this->isFullUser($user) && $this->isOwner($user, $model)) || $this->isAdmin($user);
    }
}
