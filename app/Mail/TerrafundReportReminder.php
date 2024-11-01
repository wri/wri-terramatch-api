<?php

namespace App\Mail;

class TerrafundReportReminder extends I18nMail
{
    public function __construct(int $id, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('terrafund-report-reminder.subject')
            ->setTitleKey('terrafund-report-reminder.title')
            ->setBodyKey('terrafund-report-reminder.body')
            ->setCta('terrafund-report-reminder.cta');
        $this->link = '/terrafund/programmeOverview/' . $id;

        $this->transactional = true;
    }
}
