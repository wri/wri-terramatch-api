<?php

namespace App\Listeners\v2\Organisation;

use App\Events\V2\Organisation\OrganisationUserJoinRequestEvent;
use App\Mail\OrganisationUserJoinRequested;
use Illuminate\Support\Facades\Mail;

class OrganisationUserJoinRequestSendEmail
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
     */
    public function handle(OrganisationUserJoinRequestEvent $event)
    {
        $organisation = $event->organisation;
        $emailAddressList = $organisation->owners;
        foreach ($emailAddressList as $user) {
            Mail::to($user->email_address)->send(new OrganisationUserJoinRequested($user));
        }
    }
}
