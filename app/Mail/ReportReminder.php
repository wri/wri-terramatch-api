<?php

namespace App\Mail;

use App\Models\V2\ReportModel;
use Illuminate\Support\Str;

class ReportReminder extends I18nMail
{
    private ReportModel $entity;

    public function __construct($entity, $feedback, $user)
    {
        parent::__construct($user);
        $this->entity = $entity;
        $feedback = empty($feedback) ? '(No feedback)' : $feedback;

        $this->setSubjectKey('report-reminder.subject')
            ->setTitleKey('report-reminder.title')
            ->setBodyKey('report-reminder.body')
            ->setParams([
                '{entityTypeName}' => $this->getEntityTypeName($entity),
                '{entityStatus}' => str_replace('-', ' ', $entity->status),
                '{feedback}' => $feedback,
            ]);
    }

    private function getEntityTypeName($entity): string
    {
        return ucwords(str_replace('-', ' ', Str::kebab(explode_pop('\\', get_class($entity)))));
    }
}
