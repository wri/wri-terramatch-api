<?php

namespace App\Mail;

class ApplicationSubmittedConfirmation extends Mail
{
    public function __construct(string $submissionMessage)
    {
        $this->subject = 'Your Application Has Been Submitted';
        $this->title = 'Your Application Has Been Submitted!';

        $this->body = $submissionMessage;
    }
}
