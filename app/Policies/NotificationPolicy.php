<?php

namespace App\Policies;

use App\Models\Notification as NotificationModel;
use App\Models\User as UserModel;

class NotificationPolicy extends Policy
{
    public function mark(?UserModel $user, ?NotificationModel $model = null): bool
    {
        return ($this->isAdmin($user) || $this->isTerrafundAdmin($user) || $this->isUser($user)) && $this->isOwner($user, $model);
    }

    public function readAll(?UserModel $user, ?NotificationModel $model = null): bool
    {
        return $this->isAdmin($user) || $this->isTerrafundAdmin($user) || $this->isUser($user);
    }
}
