<?php

namespace App\Mail;

class V2ProjectMonitoringNotification extends Mail
{
    public function __construct(String $name)
    {
        $this->subject = 'Project Monitoring';
        $this->title = 'Project Monitoring';
        $this->body =
            'You have been added to ' . e($name) . '.<br><br>' .

        $this->transactional = false;
    }
}
