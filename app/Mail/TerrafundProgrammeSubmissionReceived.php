<?php

namespace App\Mail;

use Illuminate\Support\Facades\Auth;

class TerrafundProgrammeSubmissionReceived extends I18nMail
{
    public function __construct(int $id, String $name)
    {
        $user = Auth::user();
        $this->setSubjectKey('terrafund-programme-submission-received.subject')
            ->setTitleKey('terrafund-programme-submission-received.title')
            ->setBodyKey('terrafund-programme-submission-received.body')
            ->setParams(['{name}' => e($name)])
            ->setCta('terrafund-programme-submission-received.cta')
            ->setUserLocale($user->locale);
        $this->link = '/admin/terrafundProgrammes/preview/?programmeId=' . $id;
        $this->transactional = true;
    }
}
