<?php

namespace App\Mail;

class UserVerification extends Mail
{
    public function __construct(String $token)
    {
        $this->subject = 'Verify Your Email Address';
        $this->title = "Verify Your Email Address";
        $this->body = "Follow this link to verify your email address. It's valid for 48 hours.";
        $this->link = "/verify?token=" . urlencode($token);
        $this->cta = "Verify Email Address";
        $this->transactional = true;
    }
}
