<?php

namespace App\Mail;

class FormSubmissionFeedbackReceived extends Mail
{
    public function __construct(String $feedback = null)
    {
        $this->subject = 'You have received feedback on your application';
        $this->title = 'You have received feedback on your application';
        $this->body =
            'Your application requires more information.';
        if (! is_null($feedback)) {
            $this->body = 'Your application requires more information. Please see comments below:<br><br>' .
            e($feedback);
        }
        $this->transactional = true;
    }
}
