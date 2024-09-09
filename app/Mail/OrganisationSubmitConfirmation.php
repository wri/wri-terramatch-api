<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class OrganisationSubmitConfirmation extends I18nMail
{
    public function __construct()
    {
        $user = Auth::user();
        $this->setSubjectKey('organisation-submit-confirmation.subject')
            ->setTitleKey('organisation-submit-confirmation.title')
            ->setBodyKey('organisation-submit-confirmation.body')
            ->setUserLocale($user->locale);
    }
}
