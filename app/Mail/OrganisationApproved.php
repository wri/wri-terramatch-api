<?php

namespace App\Mail;

class OrganisationApproved extends I18nMail
{
    public function __construct($user)
    {
        parent::__construct($user);
        $this->setSubjectKey('organisation-approved.subject')
            ->setTitleKey('organisation-approved.title')
            ->setBodyKey('organisation-approved.body')
            ->setCta('organisation-approved.cta');
        $this->link = '/auth/login';
    }
}
