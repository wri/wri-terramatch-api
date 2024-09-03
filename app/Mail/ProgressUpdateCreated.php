<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class ProgressUpdateCreated extends I18nMail
{
    public function __construct(Int $progressUpdateId, String $pitchName)
    {
        $user = Auth::user();
        $this->setSubjectKey('progress-update-created.subject')
            ->setTitleKey('progress-update-created.title')
            ->setBodyKey('progress-update-created.body')
            ->setParams(['{pitchName}' => $pitchName])
            ->setCta('progress-update-created.cta')
            ->setUserLocation($user->locale);
        $this->link = '/monitoring/report/' . $progressUpdateId;
    }
}
