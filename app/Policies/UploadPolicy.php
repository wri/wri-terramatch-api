<?php

namespace App\Policies;

use App\Models\Upload as UploadModel;
use App\Models\User as UserModel;

class UploadPolicy extends Policy
{
    public function create(?UserModel $user, ?UploadModel $model = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }

    public function update(?UserModel $user, ?UploadModel $model = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }
}
