<?php

namespace App\Mail;

use Illuminate\Support\Facades\Auth;

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
