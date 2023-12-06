<?php

namespace App\Events\V2\Organisation;

use App\Models\User;
use App\Models\V2\Organisation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrganisationRejectedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public User $rejectedBy;

    public Organisation $organisation;

    public function __construct(User $rejectedBy, Organisation $organisation)
    {
        $this->rejectedBy = $rejectedBy;
        $this->organisation = $organisation;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
