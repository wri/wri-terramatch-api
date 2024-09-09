<?php

namespace App\Mail;

use Illuminate\Support\Facades\Auth;

class FormSubmissionFinalStageApproved extends I18nMail
{
    public function __construct(String $feedback = null)
    {
        $user = Auth::user();
        $this->setSubjectKey('form-submission-final-stage-approved.subject')
            ->setTitleKey('form-submission-final-stage-approved.title')
            ->setBodyKey(! is_null($feedback) ? 'form-submission-final-stage-approved.body-feedback' : 'form-submission-final-stage-approved.body')
            ->setParams(['{feedback}' => e($feedback)])
            ->setUserLocale($user->locale);

        $this->transactional = true;
    }
}
