<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Notification;

class NotificationPolicy extends Policy
{
    public function mark(?User $user, ?Notification $model = null): bool
    {
        return ($this->isAdmin($user) || $this->isUser($user)) && $this->isOwner($user, $model);
    }
    
    public function readAll(?User $user, ?Notification $model = null): bool
    {
        return $this->isAdmin($user) || $this->isUser($user);
    }
}
