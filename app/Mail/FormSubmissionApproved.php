<?php

namespace App\Mail;

class FormSubmissionApproved extends Mail
{
    public function __construct(String $feedback = null)
    {
        $this->subject = 'Application Approved';
        $this->title = 'Your application has been approved';
        $this->body =
            'Your application has been approved.';
        if (! is_null($feedback)) {
            $this->body = 'Your application has been approved. Please see comments below:<br><br>' .
            e($feedback);
        }
        $this->transactional = true;
    }
}
