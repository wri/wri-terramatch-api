<?php

namespace App\Listeners\v2\Application;

use App\Events\V2\Application\ApplicationSubmittedEvent;
use App\Mail\ApplicationSubmittedConfirmation;
use Illuminate\Support\Facades\Mail;

class ApplicationSubmittedConfirmationSendEmail
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
    public function handle(ApplicationSubmittedEvent $event)
    {
        $formSubmission = $event->formSubmission;
        $form = $formSubmission->form;
        $user = $event->user;

        Mail::to($user->email_address)->send(new ApplicationSubmittedConfirmation(data_get($form, 'submission_message', 'Thank you for sending your application.')));
    }
}
