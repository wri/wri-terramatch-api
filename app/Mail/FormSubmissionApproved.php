<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class FormSubmissionApproved extends I18nMail
{
    public function __construct(String $feedback = null)
    {
        $user = Auth::user();
        $this->setSubjectKey('form-submission-approved.subject')
            ->setTitleKey('form-submission-approved.title')
            ->setBodyKey(! is_null($feedback) ? 'form-submission-approved.body-feedback' : 'form-submission-approved.body')
            ->setParams(['{feedback}' => e($feedback)])
            ->setUserLocale($user->locale);
        $this->transactional = true;
    }
}
