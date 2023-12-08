<?php

namespace App\Listeners\v2\Organisation;

use App\Events\V2\Organisation\OrganisationUserRequestRejectedEvent;
use App\Mail\OrganisationUserRejected;
use Illuminate\Support\Facades\Mail;

class OrganisationUserRejectedSendEmail
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
    public function handle(OrganisationUserRequestRejectedEvent $event)
    {
        $organisation = $event->organisation;
        $user = $event->user;

        Mail::to($user->email_address)->send(new OrganisationUserRejected($organisation));
    }
}
