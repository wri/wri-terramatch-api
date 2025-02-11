<?php

namespace App\Mail;

use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\Tasks\Task;
use App\StateMachines\ReportStatusStateMachine;

class TaskDigestMail extends I18nMail
{
    public function __construct($user, Task $task, bool $isManager)
    {
        parent::__construct($user);
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
        $this->link = $isManager ? '/admin#/task/'.$task->uuid.'/show' : '/project/'.$projectUUID.'/reporting-task/'.$task->uuid;
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
            return $report['status'] === ReportStatusStateMachine::NEEDS_MORE_INFORMATION || $report['update_request_status'] === ReportStatusStateMachine::AWAITING_APPROVAL;
        });
        $approvedList = $allReports->filter(function ($report) {
            return $report['status'] === ReportStatusStateMachine::APPROVED;
        });

        $types = ['due', 'started', 'awaitingApproval', 'qa', 'approved'];
        $params = [];
        $typeLabels = [
            'due' => 'Reports Due',
            'started' => 'Reports Started',
            'awaitingApproval' => 'Reports Awaiting Approval',
            'qa' => 'Reports in QA',
            'approved' => 'Reports Approved',
        ];

        $params['{reportData}'] = '';
        foreach($types as $type) {
            $list = ${$type.'List'};
            $rows = '';
            $rowCount = count($list);
            if ($rowCount > 0) {
                $count = 0;
                foreach ($list as $element) {
                    $count++;
                    if ($count === 1) {
                        $rows .= '<tr>';
                        $rows .= '<td class="border-light-gray" rowspan='.count($list).'>'.$typeLabels[$type].'</td>';
                        $rows .= '<td class="border-light-gray">' .($element['name'] ?? 'n/a') . '</td>';
                        $rows .= '<td class="border-light-gray">' . ($element['status'] ?? 'n/a') . '</td>';
                        $rows .= '<td class="border-light-gray">' . ($element['update_request_status'] ?? 'n/a') . '</td>';
                        $rows .= '<td class="border-light-gray">' . ($element['latestComment'] ?? 'n/a') . '</td>';
                        $rows .= '</tr>';
                    } else {
                        $rows .= '<tr>';
                        $rows .= '<td class="border-light-gray">' . ($element['name'] ?? 'n/a') . '</td>';
                        $rows .= '<td class="border-light-gray">' . ($element['status'] ?? 'n/a') . '</td>';
                        $rows .= '<td class="border-light-gray">' . ($element['update_request_status'] ?? 'n/a') . '</td>';
                        $rows .= '<td class="border-light-gray">' . ($element['latestComment'] ?? 'n/a') . '</td>';
                        $rows .= '</tr>';
                    }
                }
            } else {
                $rows .= '<tr>';
                $rows .= '<td class="border-light-gray" rowspan="1">'.$typeLabels[$type].'</td>';
                $rows .= '<td class="border-light-gray">n/a</td>';
                $rows .= '<td class="border-light-gray">n/a</td>';
                $rows .= '<td class="border-light-gray">n/a</td>';
                $rows .= '<td class="border-light-gray">n/a</td>';
                $rows .= '</tr>';
            }

            $params['{reportData}'] .= $rows;
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
        $mapped['name'] = $report->title ? $type.' Report: '. $report->title : $type.' Report: '. $name;
        $mapped['status'] = $report->status;
        $mapped['update_request_status'] = $report->update_request_status;
        $latestCommentAuditStatus = $report->auditStatuses()->latest()->first();
        if (! is_null($latestCommentAuditStatus)) {
            $mapped['latestComment'] = $latestCommentAuditStatus['comment'] ?? '';
            $mapped['action'] = ucwords(str_replace('-', ' ', $latestCommentAuditStatus['status']));
        } else {
            if (method_exists($report, 'audits')) {
                $latestCommentAudits = $report->audits()->latest()->first();
            }
            $mapped['latestComment'] = $latestCommentAudits['new_values']['feedback'] ?? 'n/a';
            $mapped['action'] = ucwords(str_replace('-', ' ', $latestCommentAudits['new_values']['status'] ?? 'n/a'));
        }

        return $mapped;
    }
}
