<?php

namespace App\Mail;

class V2ProjectInviteReceived extends I18nMail
{
    public function __construct(String $name, String $nameOrganisation, String $callbackUrl, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('project-invite-received.subject')
            ->setTitleKey('project-invite-received.title')
            ->setBodyKey('project-invite-received.body')
            ->setParams(['{name}' => e($name), '{nameOrganisation}' => $nameOrganisation, '{callbackUrl}' => $callbackUrl]);
        $this->link = $callbackUrl ?
        $callbackUrl . '' :
        '';
        $this->transactional = true;
        $this->invite = true;
    }
}
