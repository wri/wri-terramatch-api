<?php

namespace App\Mail;

class FormSubmissionRejected extends Mail
{
    public function __construct(String $feedback = null)
    {
        $this->subject = 'Application Status Update';
        $this->title = 'THANK YOU FOR YOUR APPLICATION';
        $this->body = 'After careful review, our team has decided your application will not move forward.';
        if (! is_null($feedback)) {
            $this->body .=
                ' Please see the comments below for more details or any follow-up resources.<br><br>' .
                e($feedback);
        }
        $this->transactional = true;
    }
}
