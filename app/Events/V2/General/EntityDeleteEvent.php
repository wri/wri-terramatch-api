<?php

namespace App\Events\V2\General;

use App\Models\User;
use App\Models\V2\UpdateRequests\ApprovalFlow;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EntityDeleteEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public User $user;

    public ApprovalFlow $entity;

    public string $title;

    public string $subTitle;

    public string $text;

    public function __construct(User $user, ApprovalFlow $entity)
    {
        $this->entity = $entity;
    }
}
