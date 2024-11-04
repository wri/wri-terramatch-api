<?php

namespace App\Mail;

use App\Models\V2\ReportModel;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TaskDigestProjectManager extends I18nMail
{
    private ReportModel $report;

    public function __construct($report, $user)
    {
        parent::__construct($user);
        $this->report = $report;

        $this->setSubjectKey('task-digest-project-manager.subject')
            ->setTitleKey('task-digest-project-manager.title')
            ->setBodyKey('task-digest-project-manager.body')
            ->setParams([
                '{projectName}' => $this->report->project->name,
                '{reportName}' => $this->report->title,
                '{reportDueAt}' => Carbon::parse($this->report->due_at)->format('d/m/Y'),
            ])
            ->setCta('task-digest-project-manager.cta');
        $this->link = $this->getViewLinkReport($this->report->shortName, $this->report->uuid);
    }

    public function getViewLinkReport($report, $uuid)
    {
        return '/admin#/' . Str::camel($report) . '/' . $uuid . '/show';
    }
}
