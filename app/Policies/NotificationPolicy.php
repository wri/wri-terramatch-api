<?php

namespace App\Policies;

use App\Models\User as UserModel;
use App\Models\Notification as NotificationModel;

class NotificationPolicy extends Policy
{
    public function mark(?UserModel $user, ?NotificationModel $model = null): bool
    {
        return ($this->isAdmin($user) || $this->isUser($user)) && $this->isOwner($user, $model);
    }
    
    public function readAll(?UserModel $user, ?NotificationModel $model = null): bool
    {
        return $this->isAdmin($user) || $this->isUser($user);
    }
}
