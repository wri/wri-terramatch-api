<?php

namespace App\Mail;

use App\Models\V2\User;

class TerrafundReportReminder extends I18nMail
{
    public function __construct(int $id, ?User $user)
    {
        $this->setSubjectKey('terrafund-report-reminder.subject')
            ->setTitleKey('terrafund-report-reminder.title')
            ->setBodyKey('terrafund-report-reminder.body')
            ->setCta('terrafund-report-reminder.cta')
            ->setUserLocation($user->locale);
        $this->link = '/terrafund/programmeOverview/' . $id;

        $this->transactional = true;
    }
}
