<?php

namespace App\Mail;

use Illuminate\Support\Facades\Auth;

class ProjectInviteReceived extends I18nMail
{
    public function __construct(String $name, String $token, String $callbackUrl = null)
    {
        parent::__construct(null);
        $this->setSubjectKey('project-invite-received.subject')
            ->setTitleKey('project-invite-received.title')
            ->setBodyKey('project-invite-received.body')
            ->setParams(['{name}' => e($name)])
            ->setCta('project-invite-received.cta');
        $this->link = $callbackUrl ?
            $callbackUrl . 'terrafund/programme/invite/accept?token=' . $token :
            '/terrafund/programme/invite/accept?token=' . $token;
    }
}
