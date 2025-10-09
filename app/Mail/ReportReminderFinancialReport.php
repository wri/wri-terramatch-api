<?php

namespace App\Mail;

use Illuminate\Support\Str;

class ReportReminderFinancialReport extends I18nMail
{
    public function __construct($financialReport, $feedback, $user)
    {
        parent::__construct($user);
        $organisationName = $financialReport->organisation->name;
        $organisationUuid = $financialReport->organisation->uuid;
        $myOrgLink = '/organisation/'.$organisationUuid;
        $callbackUrl = '/organization/'.$organisationUuid.'?tab=financial_information';
        $frontEndUrl = config('app.front_end');

        if (! Str::endsWith($frontEndUrl, '/')) {
            $frontEndUrl .= '/';
        }
        $feedback = empty($feedback) ? '(No feedback)' : $feedback;

        $this->setSubjectKey('financial-report-reminder.subject')
            ->setTitleKey('financial-report-reminder.title')
            ->setBodyKey('financial-report-reminder.body')
            ->setParams([
                '{entityModelName}' => $organisationName,
                '{dueAt}' => $financialReport->due_at->format('d M Y'),
                '{callbackUrl}' => $frontEndUrl . $myOrgLink,
                '{reportUrl}' => $frontEndUrl . $callbackUrl
            ]);
    }
}
