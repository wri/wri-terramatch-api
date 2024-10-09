<?php

namespace App\Mail;

class TargetCreated extends I18nMail
{
    public function __construct(Int $targetId, String $name, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('target-created.subject')
            ->setTitleKey('target-created.title')
            ->setBodyKey('target-created.body')
            ->setParams(['{name}' => e($name)])
            ->setCta('target-created.cta');
        $this->link = '/monitoring/review/?targetId=' . $targetId;
    }
}
