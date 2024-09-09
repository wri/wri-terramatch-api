<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class UpcomingProgressUpdate extends I18nMail
{
    public function __construct(Int $monitoringId, String $pitchName)
    {
        $user = Auth::user();
        $this->setSubjectKey('upcoming-progress-update.subject')
            ->setTitleKey('upcoming-progress-update.title')
            ->setBodyKey('upcoming-progress-update.body')
            ->setCta('upcoming-progress-update.cta')
            ->setParams(['{pitchName}' => e($pitchName)])
            ->setUserLocale($user->locale);
        $this->link = '/report/setup/' . $monitoringId;
    }
}
