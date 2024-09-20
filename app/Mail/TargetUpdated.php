<?php

namespace App\Mail;

class TargetUpdated extends I18nMail
{
    public function __construct(Int $targetId, String $name, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('target-updated.subject')
            ->setTitleKey('target-updated.title')
            ->setBodyKey('target-updated.body')
            ->setParams(['{name}' => e($name)])
            ->setCta('target-updated.cta');
        $this->link = '/monitoring/review/?targetId=' . $targetId;
    }
}
