<?php

namespace App\Mail;

use App\Models\V2\Forms\Application;

class FormSubmissionFeedbackReceived extends I18nMail
{
    public function __construct(String $feedback = null, $user, Application $application)
    {
        parent::__construct($user);
        $this->setSubjectKey('form-submission-feedback-received.subject')
            ->setTitleKey('form-submission-feedback-received.title')
            ->setBodyKey(! is_null($feedback) ? 'form-submission-feedback-received.body-feedback' : 'form-submission-feedback-received.body')
            ->setParams(['{feedback}' => e($feedback)])
            ->setCta('form-submission-feedback-received.cta');

        $this->link = '/applications/' . $application->uuid;
        $this->transactional = true;
    }
}
