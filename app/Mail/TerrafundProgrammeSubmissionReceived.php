<?php

namespace App\Mail;

class TerrafundProgrammeSubmissionReceived extends I18nMail
{
    public function __construct(int $id, String $name)
    {
        parent::__construct(null);
        $this->setSubjectKey('terrafund-programme-submission-received.subject')
            ->setTitleKey('terrafund-programme-submission-received.title')
            ->setBodyKey('terrafund-programme-submission-received.body')
            ->setParams(['{name}' => e($name)])
            ->setCta('terrafund-programme-submission-received.cta');
        $this->link = '/admin/terrafundProgrammes/preview/?programmeId=' . $id;
        $this->transactional = true;
    }
}
