<?php

namespace App\Mail;

class TargetUpdated extends Mail
{
    public function __construct(Int $targetId, String $name)
    {
        $this->subject = 'Monitoring Targets Need Review';
        $this->title = 'Monitoring Targets Need Review';
        $this->body =
            'Monitoring targets for ' . e($name) . ' have been edited and need reviewing.<br><br>' .
            'Click below to review the edited targets';
        $this->link = '/monitoring/review/?targetId=' . $targetId;
        $this->cta = 'View Monitoring Terms';
    }
}
