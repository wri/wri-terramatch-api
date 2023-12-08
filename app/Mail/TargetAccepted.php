<?php

namespace App\Mail;

class TargetAccepted extends Mail
{
    public function __construct(Int $monitoringId, String $name)
    {
        $this->subject = 'Monitoring Targets Approved';
        $this->banner = 'target_accepted';
        $this->title = 'Monitoring Targets Approved';
        $this->body =
            'Monitoring targets have been approved for ' . e($name) . '.<br><br>' .
            'Click below to view the newly unlocked project dashboard.';
        $this->link = '/monitoring/dashboard/?monitoringId=' . $monitoringId;
        $this->cta = 'View Monitored Project';
    }
}
