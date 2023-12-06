<?php

namespace App\Mail;

class TerrafundSiteAndNurseryReminder extends Mail
{
    public function __construct(int $id)
    {
        $this->subject = 'Terrafund Site & Nursery Reminder';
        $this->title = 'Terrafund Site & Nursery Reminder';

        $this->body =
            'You haven\'t created any sites or nurseries for your project, reports are due in a month.<br><br>' .
            'Click below to create.<br><br>';

        $this->link = '/terrafund/programmeOverview/' . $id;
        $this->cta = 'Create a site or nursery';

        $this->transactional = true;
    }
}
