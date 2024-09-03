<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class TargetUpdated extends I18nMail
{
    public function __construct(Int $targetId, String $name)
    {
        $user = Auth::user();
        $this->setSubjectKey('target-updated.subject')
            ->setTitleKey('target-updated.title')
            ->setBodyKey('target-updated.body')
            ->setParams(['{name}' => e($name)])
            ->setCta('target-updated.cta')
            ->setUserLocation($user->locale);
        $this->link = '/monitoring/review/?targetId=' . $targetId;
    }
}
