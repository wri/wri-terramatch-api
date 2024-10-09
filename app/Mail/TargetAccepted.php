<?php

namespace App\Mail;

class TargetAccepted extends I18nMail
{
    public function __construct(Int $monitoringId, String $name, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('target-accepted.subject')
            ->setTitleKey('target-accepted.title')
            ->setBodyKey('target-accepted.body')
            ->setParams(['{name}' => e($name)])
            ->setCta('target-accepted.cta');
        $this->banner = 'target_accepted';
        $this->link = '/monitoring/dashboard/?monitoringId=' . $monitoringId;
    }
}
