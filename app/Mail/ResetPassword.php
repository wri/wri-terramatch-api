<?php

namespace App\Mail;

class ResetPassword extends Mail
{
    public function __construct(String $token, string $callbackUrl = null)
    {
        $this->subject = 'RESET YOUR PASSWORD';
        $this->title = 'RESET YOUR PASSWORD';
        $this->body =
            "You've requested a password reset.<br><br>" .
            "Follow this link to reset your password. It's valid for 2 hours.<br><br>" .
            'If you have any questions, feel free to message us at info@terramatch.org.';
        $this->link = $callbackUrl ?
            $callbackUrl . urlencode($token) :
            '/passwordReset?token=' . urlencode($token);
        $this->cta = 'Reset Password';
        $this->transactional = true;
    }
}
