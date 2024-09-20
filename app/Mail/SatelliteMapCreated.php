<?php

namespace App\Mail;

class SatelliteMapCreated extends I18nMail
{
    public function __construct(Int $satelliteMapId, String $name, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('satellite-map-created.subject')
            ->setTitleKey('satellite-map-created.title')
            ->setBodyKey('satellite-map-created.body')
            ->setParams(['{name}' => e($name)])
            ->setCta('satellite-map-created.cta');
        $this->link = '/monitoring/dashboard/?satelliteId=' . $satelliteMapId;
    }
}
