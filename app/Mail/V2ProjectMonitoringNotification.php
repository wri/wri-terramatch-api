<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class V2ProjectMonitoringNotification extends I18nMail
{
    public function __construct(String $name, $callbackUrl)
    {
        $user = Auth::user();
        $this->setSubjectKey('v2-project-monitoring-notification.subject')
            ->setTitleKey('v2-project-monitoring-notification.title')
            ->setBodyKey('v2-project-monitoring-notification.body')
            ->setParams(['{name}' => $name, '{callbackUrl}' => $callbackUrl])
            ->setUserLocale($user->locale);

        $this->transactional = false;
        $this->monitoring = true;

    }
}
