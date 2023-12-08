<?php

namespace App\Mail;

class TargetCreated extends Mail
{
    public function __construct(Int $targetId, String $name)
    {
        $this->subject = 'Monitoring Targets Set';
        $this->title = 'Monitoring Targets Set';
        $this->body =
            'You have been sent monitoring targets to approve for ' . e($name) . '.<br><br>' .
            'Click below to view these targets.<br><br>' .
            'Note: you may need to update your funding status on TerraMatch to view.';
        $this->link = '/monitoring/review/?targetId=' . $targetId;
        $this->cta = 'View Monitoring Terms';
    }
}
