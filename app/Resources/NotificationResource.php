<?php

namespace App\Resources;

use App\Models\Notification as NotificationModel;

class NotificationResource extends Resource
{
    public function __construct(NotificationModel $notification)
    {
        $this->id = $notification->id;
        $this->user_id = $notification->user_id;
        $this->title = $notification->title;
        $this->body = $notification->body;
        $this->action = $notification->action;
        $this->referenced_model = $notification->referenced_model;
        $this->referenced_model_id = $notification->referenced_model_id;
        $this->unread = $notification->unread;
        $this->created_at = $notification->created_at;
    }
}
