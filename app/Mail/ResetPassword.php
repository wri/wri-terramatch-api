<?php

namespace App\Mail;

class ResetPassword extends Mail
{
    public function __construct(String $token)
    {
        $this->subject = 'Reset Your Password';
        $this->title = "Reset Your Password";
        $this->body =
            "You've requested a password reset.<br><br>" .
            "Follow this link to reset your password. It's valid for 2 hours.";
        $this->link =  "/passwordReset?token=" . urlencode($token);
        $this->cta = "Reset Password";
        $this->transactional = true;
    }
}
