<?php

namespace App\Mail;

class FormSubmissionFinalStageApproved extends I18nMail
{
    public function __construct(String $feedback = null, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('form-submission-final-stage-approved.subject')
            ->setTitleKey('form-submission-final-stage-approved.title')
            ->setBodyKey(! is_null($feedback) ? 'form-submission-final-stage-approved.body-feedback' : 'form-submission-final-stage-approved.body')
            ->setParams(['{feedback}' => e($feedback)]);

        $this->transactional = true;
    }
}
