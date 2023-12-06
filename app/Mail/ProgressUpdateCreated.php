<?php

namespace App\Mail;

class ProgressUpdateCreated extends Mail
{
    public function __construct(Int $progressUpdateId, String $pitchName)
    {
        $this->subject = 'Report Received';
        $this->banner = 'progress_update_created';
        $this->title = 'Report Received';
        $this->body =
            ' A new progress update report has been submitted for ' . e($pitchName) . '.<br><br>' .
            'Click below to view the report.';
        $this->link = '/monitoring/report/' . $progressUpdateId;
        $this->cta = 'View Report';
    }
}
