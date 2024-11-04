<?php

namespace App\Mail;

use App\Models\V2\EntityModel;
use Illuminate\Support\Str;

class TaskDigestProjectNotificationMail extends I18nMail
{
    private EntityModel $entity;

    public function __construct($entity, $user, $projectName, $report)
    {
        parent::__construct($user);
        $this->setSubjectKey('task-digest-project-manager.subject')
            ->setTitleKey('task-digest-project-manager.title')
            ->setCta('task-digest-project-manager.cta')
            ->setBodyKey('task-digest-project-manager.body')
            ->setBodyParams([
                '{projectName}' => $projectName,
                '{reportName}' => $report->title,
                '{reportDueAt}' => $report->due_at,
            ]);
        $this->transactional = true;
        $this->link = '/admin#/' . Str::camel($entity) . '/' . $report->uuid . '/show';
    }
}
