<?php

namespace App\Listeners\v2\General;

use App\Events\V2\General\EntityDeleteEvent;
use App\Models\V2\Action;

class DeleteAction
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\V2\General\EntityDeleteEvent  $event
     * @return void
     */
    public function handle(EntityDeleteEvent $event)
    {
        Action::where('targetable_type', get_class($event->entity))
            ->where('targetable_id', $event->entity->id)
            ->delete();
    }
}
