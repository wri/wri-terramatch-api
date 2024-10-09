<?php

namespace App\Listeners\v2\Organisation;

use App\Events\V2\Organisation\OrganisationUserRequestApprovedEvent;
use App\Mail\OrganisationUserApproved;
use Illuminate\Support\Facades\Mail;

class OrganisationUserApprovedSendEmail
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
    public function handle(OrganisationUserRequestApprovedEvent $event)
    {
        $organisation = $event->organisation;
        $user = $event->user;

        Mail::to($user->email_address)->send(new OrganisationUserApproved($organisation, $user));
    }
}
