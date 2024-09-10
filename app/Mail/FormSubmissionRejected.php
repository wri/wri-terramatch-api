<?php

namespace App\Mail;

use Illuminate\Support\Facades\Auth;

class FormSubmissionRejected extends I18nMail
{
    public function __construct(String $feedback = null, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('form-submission-rejected.subject')
            ->setTitleKey('form-submission-rejected.title')
            ->setBodyKey(! is_null($feedback) ? 'form-submission-rejected.body-feedback' : 'form-submission-rejected.body')
            ->setParams(['{feedback}' => e($feedback)]);

        $this->transactional = true;
    }
}
