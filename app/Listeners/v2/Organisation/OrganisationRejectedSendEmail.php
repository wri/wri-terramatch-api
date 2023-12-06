<?php

namespace App\Listeners\v2\Organisation;

use App\Events\V2\Organisation\OrganisationRejectedEvent;
use App\Mail\OrganisationRejected;
use App\Models\V2\User;
use Illuminate\Support\Facades\Mail;

class OrganisationRejectedSendEmail
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
    public function handle(OrganisationRejectedEvent $event)
    {
        $organisation = $event->organisation;
        $user = User::where('organisation_id', $organisation->id)->firstOrFail();

        Mail::to($user->email_address)->send(new OrganisationRejected($organisation));
    }
}
