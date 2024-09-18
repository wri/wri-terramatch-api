<?php

namespace App\Mail;

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use App\StateMachines\ReportStatusStateMachine;

class TaskDigestMail extends I18nMail
{
    public function __construct(Task $task)
    {
        parent::__construct(null);
        $params = $this->getBodyParams($task);
        $this->setSubjectKey('task-digest.subject')
            ->setSubjectParams([
                '{projectName}' => $task->project->name,
                '{date}' => now()->format('d/m/Y'),
            ])
            ->setTitleKey('task-digest.title')
            ->setTitleParams([
                '{date}' => $task->due_at->format('d/m/Y'),
            ])
            ->setCta('task-digest.cta')
            ->setBodyKey('task-digest.body')
            ->setBodyParams($params);
        $this->transactional = true;
        $projectUUID = $task->project()->first()->uuid;
        $this->link = '/project/'.$projectUUID.'?tab=reporting-tasks';
    }

    private function getBodyParams(Task $task): array
    {
        $allReports = collect([
            $task->projectReport()->get()->map([$this, 'mapReport']),
            $task->siteReports()->get()->map([$this, 'mapReport']),
            $task->nurseryReports()->get()->map([$this, 'mapReport']),
        ])->flatten(1);
        $dueList = $allReports->filter(function ($report) {
            return $report['status'] === ReportStatusStateMachine::DUE;
        });
        $startedList = $allReports->filter(function ($report) {
            return $report['status'] === ReportStatusStateMachine::STARTED;
        });
        $completedList = $allReports->filter(function ($report) {
            return $report['status'] === ReportStatusStateMachine::APPROVED;
        });
        $awaitingApprovalList = $allReports->filter(function ($report) {
            return $report['status'] === ReportStatusStateMachine::AWAITING_APPROVAL;
        });
        $qaList = $allReports->filter(function ($report) {
            return $report['status'] === ReportStatusStateMachine::NEEDS_MORE_INFORMATION;
        });
        $approvedList = $allReports->filter(function ($report) {
            return $report['status'] === ReportStatusStateMachine::APPROVED;
        });
        $types = ['due', 'started', 'awaitingApproval', 'qa', 'approved'];
        $params = [];
        foreach($types as $type) {
            $list = ${$type.'List'};
            $element = [];
            if (count($list) > 0) {
                $element = $list->first();
            }
            $reportNameVar = '{'.$type.'ReportName}';
            $actionVar = '{'.$type.'Action}';
            $latestCommentVar = '{'.$type.'LatestComment}';

            $params[$reportNameVar] = $element['name'] ?? 'n/a';
            $params[$actionVar] = $element['action'] ?? 'n/a';
            $params[$latestCommentVar] = $element['latestComment'] ?? 'n/a';
        }

        return $params;
    }

    public function mapReport($report)
    {
        $mapped = [];
        if ($report instanceof ProjectReport) {
            $type = 'Project';
            $name = $report->project()->pluck('name')[0];
        } elseif ($report instanceof SiteReport) {
            $type = 'Site';
            $name = $report->site()->pluck('name')[0];
        } elseif ($report instanceof NurseryReport) {
            $type = 'Nursery';
            $name = $report->nursery()->pluck('name')[0];
        }
        $mapped['name'] = $type.' Report: '. $name;
        $mapped['status'] = $report->status;
        $latestComment = $report->auditStatuses()->latest()->first();
        if (! is_null($latestComment)) {
            $mapped['latestComment'] = $latestComment->comment;
            $mapped['action'] = $latestComment->status;
        } else {
            $mapped['latestComment'] = '';
            $mapped['action'] = '';
        }

        return $mapped;
    }
}
