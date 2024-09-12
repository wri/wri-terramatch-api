<?php

namespace App\Mail;

class TerrafundSiteAndNurseryReminder extends I18nMail
{
    public function __construct(int $id, $user)
    {
        parent::__construct($user);
        $this->setSubjectKey('terrafund-site-and-nursery-reminder.subject')
            ->setTitleKey('terrafund-site-and-nursery-reminder.title')
            ->setBodyKey('terrafund-site-and-nursery-reminder.body')
            ->setCta('terrafund-site-and-nursery-reminder.cta');

        $this->link = '/terrafund/programmeOverview/' . $id;

        $this->transactional = true;
    }
}
