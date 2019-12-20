<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Upload;

class UploadPolicy extends Policy
{
    public function create(?User $user, ?Upload $model = null): bool
    {
        return $this->isUser($user) || $this->isAdmin($user);
    }
}
