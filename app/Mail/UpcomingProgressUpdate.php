<?php

namespace App\Mail;

class UpcomingProgressUpdate extends I18nMail
{
    public function __construct(Int $monitoringId, String $pitchName, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('upcoming-progress-update.subject')
            ->setTitleKey('upcoming-progress-update.title')
            ->setBodyKey('upcoming-progress-update.body')
            ->setCta('upcoming-progress-update.cta')
            ->setParams(['{pitchName}' => e($pitchName)]);
        $this->link = '/report/setup/' . $monitoringId;
    }
}
