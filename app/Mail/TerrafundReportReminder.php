<?php

namespace App\Mail;
use Illuminate\Support\Facades\Auth;

class TerrafundReportReminder extends I18nMail
{
    public function __construct(int $id)
    {
        $user = Auth::user();
        $this->setSubjectKey('terrafund-report-reminder.subject')
            ->setTitleKey('terrafund-report-reminder.title')
            ->setBodyKey('terrafund-report-reminder.body')
            ->setCta('terrafund-report-reminder.cta')
            ->setUserLocale($user->locale);
        $this->link = '/terrafund/programmeOverview/' . $id;

        $this->transactional = true;
    }
}
