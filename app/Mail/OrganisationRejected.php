<?php

namespace App\Mail;

class OrganisationRejected extends I18nMail
{
    public function __construct($user)
    {
        parent::__construct($user);
        $this->setSubjectKey('organisation-rejected.subject')
            ->setTitleKey('organisation-rejected.title')
            ->setBodyKey('organisation-rejected.body');
    }
}
