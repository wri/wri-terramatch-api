<?php

namespace App\Mail;

class ProgrammeInviteReceived extends Mail
{
    public function __construct(String $name, String $token, string $callbackUrl = null)
    {
        $this->subject = 'Programme Monitoring Invite';
        $this->title = 'PROJECT MONITORING INVITE';
        $this->body =
            'You have been sent an invite to monitor ' . e($name) . '.<br><br>' .
            'Click below to accept the invite.<br><br>';
        $this->link = $callbackUrl ?
            $callbackUrl . 'programme/invite/accept?token=' . $token :
            '/programme/invite/accept?token=' . $token;
        $this->cta = 'ACCEPT MONITORING INVITE';
    }
}
