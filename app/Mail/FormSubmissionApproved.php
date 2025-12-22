<?php

namespace App\Mail;

class FormSubmissionApproved extends I18nMail
{
    public function __construct(String $feedback = null, $user, $applicationUuid)
    {
        parent::__construct($user);
        $this->setSubjectKey('form-submission-approved.subject')
            ->setTitleKey('form-submission-approved.title')
            ->setBodyKey(! is_null($feedback) ? 'form-submission-approved.body-feedback' : 'form-submission-approved.body')
            ->setParams(['{feedback}' => e($feedback)]);
        $this->setCta('form-submission-approved.cta');
        $this->link = '/applications/' . $applicationUuid;
        $this->transactional = true;
    }
}
