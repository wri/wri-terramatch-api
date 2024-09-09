<?php

namespace App\Mail;

use App\Models\V2\User;

class TerrafundSiteAndNurseryReminder extends I18nMail
{
    public function __construct(int $id, ?User $user)
    {
        $this->setSubjectKey('terrafund-site-and-nursery-reminder.subject')
            ->setTitleKey('terrafund-site-and-nursery-reminder.title')
            ->setBodyKey('terrafund-site-and-nursery-reminder.body')
            ->setCta('terrafund-site-and-nursery-reminder.cta')
            ->setUserLocale($user->locale);

        $this->link = '/terrafund/programmeOverview/' . $id;

        $this->transactional = true;
    }
}
