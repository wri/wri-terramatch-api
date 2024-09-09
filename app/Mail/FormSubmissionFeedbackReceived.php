<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class FormSubmissionFeedbackReceived extends I18nMail
{
    public function __construct(String $feedback = null)
    {
        $user = Auth::user();
        $this->setSubjectKey('form-submission-feedback-received.subject')
            ->setTitleKey('form-submission-feedback-received.title')
            ->setBodyKey(! is_null($feedback) ? 'form-submission-feedback-received.body-feedback' : 'form-submission-feedback-received.body')
            ->setParams(['{feedback}' => e($feedback)])
            ->setUserLocale($user->locale);

        $this->transactional = true;
    }
}
