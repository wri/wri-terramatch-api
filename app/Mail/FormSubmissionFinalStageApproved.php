<?php

namespace App\Mail;

class FormSubmissionFinalStageApproved extends Mail
{
    public function __construct(String $feedback = null)
    {
        $this->subject = 'Application Approved';
        $this->title = 'Your application has been approved';
        if (! is_null($feedback)) {
            $this->body =
                'Your application has successfully passed all stages of our evaluation process and has been officially approved. Please see the comments below:<br><br>' .
                e($feedback) .
                '<br><br>If you have any immediate queries, please do not hesitate to reach out to our dedicated support team.';
        } else {
            $this->body =
            'Your application has successfully passed all stages of our evaluation process and has been officially approved.
            If you have any immediate queries, please do not hesitate to reach out to our support team.';
        }
        $this->transactional = true;
    }
}
