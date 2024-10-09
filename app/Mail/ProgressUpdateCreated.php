<?php

namespace App\Mail;

class ProgressUpdateCreated extends I18nMail
{
    public function __construct(Int $progressUpdateId, String $pitchName, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('progress-update-created.subject')
            ->setTitleKey('progress-update-created.title')
            ->setBodyKey('progress-update-created.body')
            ->setParams(['{pitchName}' => $pitchName])
            ->setCta('progress-update-created.cta');
        $this->link = '/monitoring/report/' . $progressUpdateId;
    }
}
