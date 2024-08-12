<?php

namespace App\Events\V2\Organisation;

use App\Models\V2\Organisation;
use App\Models\V2\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrganisationUserRequestApprovedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public User $approvedBy;

    public User $user;

    public Organisation $organisation;

    public function __construct(User $approvedBy, User $user, Organisation $organisation)
    {
        $this->approvedBy = $approvedBy;
        $this->user = $user;
        $this->organisation = $organisation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    //    public function broadcastOn()
    //    {
    //        return new PrivateChannel('channel-name');
    //    }
}
