<?php

namespace App\Mail;

class FormSubmissionRejected extends Mail
{
    public function __construct(String $feedback = null)
    {
        $this->subject = 'Application Rejected';
        $this->title = 'Your application has been rejected';
        $this->body =
            'Your application has been rejected.';
        if (! is_null($feedback)) {
            $this->body = 'Your application has been rejected. Please see comments below:<br><br>' .
            e($feedback);
        }
        $this->transactional = true;
    }
}
