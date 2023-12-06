<?php

namespace App\Mail;

class FormSubmissionSubmitted extends Mail
{
    public function __construct()
    {
        $this->subject = 'You have submitted an application';
        $this->title = 'You have submitted an application';
        $this->body =
            'Your application has been submitted';
        $this->transactional = true;
    }
}
