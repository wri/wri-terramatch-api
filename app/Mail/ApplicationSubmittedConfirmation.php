<?php

namespace App\Mail;

class ApplicationSubmittedConfirmation extends I18nMail
{
    public function __construct(string $submissionMessage, $user)
    {
        parent::__construct($user);

        $this->setSubjectKey('application-submitted-confirmation.subject')
            ->setTitleKey('application-submitted-confirmation.title');

        if (empty($submissionMessage)) {
            $this->setBodyKey('application-submitted-confirmation.body');
        } else {
            $this->body = $submissionMessage;
        }
    }
}
