<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class TargetAccepted extends I18nMail
{
    public function __construct(Int $monitoringId, String $name)
    {
        $user = Auth::user();
        $this->setSubjectKey('target-accepted.subject')
            ->setTitleKey('target-accepted.title')
            ->setBodyKey('target-accepted.body')
            ->setParams(['{name}' => e($name)])
            ->setCta('target-accepted.cta')
            ->setUserLocale($user->locale);
        $this->banner = 'target_accepted';
        $this->link = '/monitoring/dashboard/?monitoringId=' . $monitoringId;
    }
}
