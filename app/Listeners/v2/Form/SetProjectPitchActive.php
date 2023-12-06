<?php

namespace App\Listeners\v2\Form;

use App\Events\V2\Application\ApplicationSubmittedEvent;
use App\Models\V2\ProjectPitch;

class SetProjectPitchActive
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
     * @param  \App\Events\v2\application\ApplicationSubmittedEvent  $event
     * @return void
     */
    public function handle(ApplicationSubmittedEvent $event)
    {
        $event->formSubmission->projectPitch()->update([
            'status' => ProjectPitch::STATUS_ACTIVE,
        ]);
    }
}
