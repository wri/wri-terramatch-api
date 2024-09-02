<?php

namespace App\Mail;

class ApplicationSubmittedConfirmation extends I18nMail
{
    public function __construct(string $submissionMessage, $user)
    {
        $this->setSubjectKey('application-submitted-confirmation.subject')
        ->setTitleKey('application-submitted-confirmation.title')
        ->setUserLocation($user->locale);
        // ->setBodyKey('application-submitted-confirmation.body');
        $this->body = $submissionMessage;
    }
}
