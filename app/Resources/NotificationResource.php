<?php

namespace App\Resources;

use App\Models\Notification as NotificationModel;

class NotificationResource extends Resource
{
    public $id = null;
    public $user_id = null;
    public $title = null;
    public $body = null;
    public $action = null;
    public $referenced_type = null;
    public $unread = null;
    public $created_at = null;

    public function __construct(NotificationModel $notification)
    {
        $this->id = $notification->id;
        $this->user_id = $notification->user_id;
        $this->title = $notification->title;
        $this->body = $notification->body;
        $this->action = $notification->action;
        $this->referenced_type = $notification->referenced_type;
        $this->referenced_action_id = $notification->referenced_action_id;
        $this->unread = $notification->unread;
        $this->created_at = $notification->created_at;
    }
}
