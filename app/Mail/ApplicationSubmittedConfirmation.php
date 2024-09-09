<?php

namespace App\Mail;

class ApplicationSubmittedConfirmation extends I18nMail
{
    public function __construct(string $submissionMessage, $user)
    {
        $this->setSubjectKey('application-submitted-confirmation.subject')
        ->setTitleKey('application-submitted-confirmation.title')
        ->setUserLocale($user->locale);

        if (empty($submissionMessage)) {
            $this->setBodyKey('application-submitted-confirmation.body');    
        } else {
            $this->body = $submissionMessage;
        }
    }
}
