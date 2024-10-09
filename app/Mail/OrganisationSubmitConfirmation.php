<?php

namespace App\Mail;

class OrganisationSubmitConfirmation extends I18nMail
{
    public function __construct($user)
    {
        parent::__construct($user);
        $this->setSubjectKey('organisation-submit-confirmation.subject')
            ->setTitleKey('organisation-submit-confirmation.title')
            ->setBodyKey('organisation-submit-confirmation.body');
    }
}
