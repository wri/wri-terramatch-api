<?php

namespace App\Mail;

class SatelliteMapCreated extends Mail
{
    public function __construct(Int $satelliteMapId, String $name)
    {
        $this->subject = 'Remote Sensing Map Received';
        $this->title = 'Remote Sensing Map Received';
        $this->body =
            'WRI has submitted an updated remote sensing map for ' . e($name) . '.<br><br>' .
            'Click below to view the map.';
        $this->link = '/monitoring/dashboard/?satelliteId=' . $satelliteMapId;
        $this->cta = 'View Monitored Project';
    }
}
