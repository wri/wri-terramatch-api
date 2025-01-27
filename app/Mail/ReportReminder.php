<?php

namespace App\Mail;

use App\Models\V2\ReportModel;
use App\Models\V2\Sites\Site;
use Illuminate\Support\Str;

class ReportReminder extends I18nMail
{
    private ReportModel $entity;

    public function __construct($entity, $feedback, $user)
    {
        parent::__construct($user);
        $this->entity = $entity;
        $entityName = '';
        $task = $entity->task;
        $projectUUID = $task->project()->value('uuid');
        $entityClass = Str::kebab(explode_pop('\\', get_class($entity)));
        $callbackUrl = '/project/'.$projectUUID.'/reporting-task/'.$task->uuid;
        $frontEndUrl = config('app.front_end');

        if ($entityClass == 'project-report') {
            $entityName = $entity->project->name;
        }
        if ($entityClass == 'site-report') {
            $entityName = Site::find($entity->site_id)?->value('name');
        }
        if ($entityClass == 'nursery-report') {
            $entityName = $entity->nursery->name;
        }

        if (! Str::endsWith($frontEndUrl, '/')) {
            $frontEndUrl .= '/';
        }
        $feedback = empty($feedback) ? '(No feedback)' : $feedback;

        $this->setSubjectKey('report-reminder.subject')
            ->setTitleKey('report-reminder.title')
            ->setBodyKey('report-reminder.body')
            ->setParams([
                '{entityTypeName}' => $this->getEntityTypeName($entity),
                '{entityModelName}' => $entityName,
                '{entityStatus}' => str_replace('-', ' ', $entity->status),
                '{callbackUrl}' => $frontEndUrl . $callbackUrl,
                '{feedback}' => $feedback,
            ]);
    }

    private function getEntityTypeName($entity): string
    {
        return ucwords(str_replace('-', ' ', Str::kebab(explode_pop('\\', get_class($entity)))));
    }
}
