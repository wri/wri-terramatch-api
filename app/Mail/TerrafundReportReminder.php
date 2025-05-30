<?php

namespace App\Mail;

use App\Models\V2\Tasks\Task;

class TerrafundReportReminder extends I18nMail
{
    public function __construct($reportingTaskUuid, $user)
    {
        parent::__construct($user);

        $task = Task::where('uuid', $reportingTaskUuid)->first();
        $project = $task->project;
        $projectUuid = $project->uuid;

        $projectName = $project->name;
        $dueDate = $task->due_at->format('F j');

        $this->setSubjectKey('terrafund-report-reminder.subject')
            ->setTitleKey('terrafund-report-reminder.title')
            ->setBodyKey('terrafund-report-reminder.body')
            ->setCta('terrafund-report-reminder.cta')
            ->setBodyParams([
                '{projectName}' => $projectName,
                '{dueDate}' => $dueDate,
            ]);

        $this->link = '/project/' . $projectUuid . '/reporting-task/' . $reportingTaskUuid;

        $this->transactional = true;
    }
}
