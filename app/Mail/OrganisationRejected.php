<?php

namespace App\Mail;

use Illuminate\Support\Facades\Auth;

class OrganisationRejected extends I18nMail
{
    public function __construct()
    {
        $user = Auth::user();
        $this->setSubjectKey('organisation-rejected.subject')
            ->setTitleKey('organisation-rejected.title')
            ->setBodyKey('organisation-rejected.body')
            ->setUserLocale($user->locale);
    }
}
