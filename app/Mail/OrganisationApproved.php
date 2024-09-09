<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class OrganisationApproved extends I18nMail
{
    public function __construct()
    {
        $user = Auth::user();
        $this->setSubjectKey('organisation-approved.subject')
            ->setTitleKey('organisation-approved.title')
            ->setBodyKey('organisation-approved.body')
            ->setCta('organisation-approved.cta')
            ->setUserLocale($user->locale);
        $this->link = '/auth/login';
    }
}
