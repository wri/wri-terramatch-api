<?php

namespace App\Mail;

use Illuminate\Support\Facades\Auth;

class SatelliteMapCreated extends I18nMail
{
    public function __construct(Int $satelliteMapId, String $name)
    {
        $user = Auth::user();
        $this->setSubjectKey('satellite-map-created.subject')
            ->setTitleKey('satellite-map-created.title')
            ->setBodyKey('satellite-map-created.body')
            ->setParams(['{name}' => e($name)])
            ->setCta('satellite-map-created.cta')
            ->setUserLocale($user->locale);
        $this->link = '/monitoring/dashboard/?satelliteId=' . $satelliteMapId;
    }
}
