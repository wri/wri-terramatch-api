<?php

namespace App\Mail;

class UpcomingProgressUpdate extends Mail
{
    public function __construct(Int $monitoringId, String $pitchName)
    {
        $this->subject = 'Report Due';
        $this->title = 'Report Due';
        $this->body =
            ' You are due to submit a progress update report for ' . e($pitchName) . ' in 30 days.<br><br>' .
            'Click below to to create your report.';
        $this->link = '/report/setup/' . $monitoringId;
        $this->cta = 'Create Report';
    }
}
