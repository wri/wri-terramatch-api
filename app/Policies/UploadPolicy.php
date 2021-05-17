<?php

namespace App\Policies;

use App\Models\User as UserModel;
use App\Models\Upload as UploadModel;

class UploadPolicy extends Policy
{
    public function create(?UserModel $user, ?UploadModel $model = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }
}
