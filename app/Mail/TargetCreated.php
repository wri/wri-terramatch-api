<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class TargetCreated extends I18nMail
{
    public function __construct(Int $targetId, String $name)
    {
        $user = Auth::user();
        $this->setSubjectKey('target-created.subject')
            ->setTitleKey('target-created.title')
            ->setBodyKey('target-created.body')
            ->setParams(['{name}' => e($name)])
            ->setCta('target-created.cta')
            ->setUserLocale($user->locale);
        $this->link = '/monitoring/review/?targetId=' . $targetId;
    }
}
