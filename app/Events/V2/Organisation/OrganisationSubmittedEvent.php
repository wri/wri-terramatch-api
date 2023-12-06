<?php

namespace App\Events\V2\Organisation;

use App\Models\V2\Organisation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrganisationSubmittedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
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
