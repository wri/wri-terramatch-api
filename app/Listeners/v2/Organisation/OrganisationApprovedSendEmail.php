<?php

namespace App\Listeners\v2\Organisation;

use App\Events\v2\organisation\OrganisationApprovedEvent;
use App\Mail\OrganisationApproved;
use App\Models\V2\User;
use Illuminate\Support\Facades\Mail;

class OrganisationApprovedSendEmail
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
    public function handle(OrganisationApprovedEvent $event)
    {
        $organisation = $event->organisation;
        $user = User::where('organisation_id', $organisation->id)->firstOrFail();

        Mail::to($user->email_address)->send(new OrganisationApproved($organisation, $user));
    }
}
