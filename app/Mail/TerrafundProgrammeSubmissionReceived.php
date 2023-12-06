<?php

namespace App\Mail;

class TerrafundProgrammeSubmissionReceived extends Mail
{
    public function __construct(int $id, String $name)
    {
        $this->subject = 'Terrafund Programme Report Submitted';
        $this->title = 'Terrafund Programme Report Submitted';
        $this->body =
            'A new report has been submitted!<br><br>' .
            e($name) . ' has a new report submited.<br><br>' .
            'Click below to view and edit the report.<br><br>';
        $this->link = '/admin/terrafundProgrammes/preview/?programmeId=' . $id;
        $this->cta = 'View Report';
        $this->transactional = true;
    }
}
