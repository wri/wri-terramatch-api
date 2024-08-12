<?php

namespace App\Mail;

class ApplicationSubmittedConfirmation extends I18nMail
{
    public function __construct(string $submissionMessage)
    {
        $this->setSubjectKey('application-submitted-confirmation.subject')
        ->setTitleKey('application-submitted-confirmation.title');

        // $this->subject = 'Your Application Has Been Submitted';
        // $this->title = 'Your Application Has Been Submitted!';

        $this->body = $submissionMessage;
    }
}
