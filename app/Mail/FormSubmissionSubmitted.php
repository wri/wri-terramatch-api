<?php

namespace App\Mail;

use Illuminate\Support\Facades\Auth;

class FormSubmissionSubmitted extends I18nMail
{
    public function __construct($user)
    {
        parent::__construct($user);
        $this->setSubjectKey('form-submission-submitted.subject')
            ->setTitleKey('form-submission-submitted.title')
            ->setBodyKey('form-submission-submitted.body');

        $this->transactional = true;
    }
}
