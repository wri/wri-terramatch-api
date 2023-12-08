<?php

namespace App\Listeners\v2\Organisation;

use App\Events\V2\Organisation\OrganisationSubmittedEvent;
use App\Mail\OrganisationSubmitConfirmation;
use App\Models\V2\User;
use Illuminate\Support\Facades\Mail;

class OrganisationSubmittedConfirmationSendEmail
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
    public function handle(OrganisationSubmittedEvent $event)
    {
        $organisation = $event->organisation;
        $user = User::where('organisation_id', $organisation->id)->firstOrFail();

        Mail::to($user->email_address)->send(new OrganisationSubmitConfirmation($organisation));
    }
}
