<?php

namespace App\Mail;

class OrganisationUserJoinRequested extends I18nMail
{
    public function __construct($user)
    {
        parent::__construct($user);
        $this->setSubjectKey('organisation-user-join-requested.subject')
            ->setTitleKey('organisation-user-join-requested.title')
            ->setBodyKey('organisation-user-join-requested.body');
    }
}
