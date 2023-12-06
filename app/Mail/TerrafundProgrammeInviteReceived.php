<?php

namespace App\Mail;

class TerrafundProgrammeInviteReceived extends Mail
{
    public function __construct(String $name, String $token, String $callbackUrl = null)
    {
        $this->subject = 'Terrafund Programme Invite';
        $this->title = 'Terrafund Programme Invite';
        $this->body =
            'You have been sent an invite to join ' . e($name) . '.<br><br>' .
            'Click below to accept the invite.<br><br>';
        $this->link = $callbackUrl ?
            $callbackUrl . 'terrafund/programme/invite/accept?token=' . $token :
            '/terrafund/programme/invite/accept?token=' . $token;
        $this->cta = 'Accept invite';
    }
}
