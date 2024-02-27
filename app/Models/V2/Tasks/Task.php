<?php

namespace App\Models\V2\Tasks;

use App\Exceptions\InvalidStatusException;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use App\Models\V2\Nurseries\NurseryReport;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\Projects\ProjectReport;
use App\Models\V2\Sites\SiteReport;
use App\Models\V2\UpdateRequests\UpdateRequest;
use App\StateMachines\ReportStatusStateMachine;
use App\StateMachines\TaskStatusStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasStatus;
    use HasStateMachines;

    public $table = 'v2_tasks';

    public $stateMachines = [
        'status' => TaskStatusStateMachine::class,
    ];

    public static $statuses = [
        TaskStatusStateMachine::DUE => 'Due',
        TaskStatusStateMachine::AWAITING_APPROVAL => 'Awaiting approval',
        TaskStatusStateMachine::NEEDS_MORE_INFORMATION => 'Needs more information',
        TaskStatusStateMachine::APPROVED => 'Approved',
    ];

    public const COMPLETE_STATUSES = [
        TaskStatusStateMachine::AWAITING_APPROVAL,
        TaskStatusStateMachine::APPROVED
    ];

    protected $fillable = [
        'organisation_id',
        'project_id',
        'title',
        'status',
        'period_key',
        'due_at',
    ];

    public $casts = [
        'due_at' => 'datetime',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function projectReport(): HasOne
    {
        return $this->hasOne(ProjectReport::class);
    }

    public function siteReports(): HasMany
    {
        return $this->hasMany(SiteReport::class);
    }

    public function nurseryReports(): HasMany
    {
        return $this->hasMany(NurseryReport::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function scopeIsIncomplete(Builder $query): Builder
    {
        return $query->whereNotIn('status', self::COMPLETE_STATUSES);
    }

    /**
     * @throws InvalidStatusException
     */
    public function submitForApproval ()
    {
        if (!$this->status()->canBe(TaskStatusStateMachine::AWAITING_APPROVAL)) {
            throw new InvalidStatusException(
                'Task is not in a state that can be moved to awaiting approval'
            );
        }

        // First, make sure all reports are either complete, or completable
        $reports = array_merge([$this->projectReport], $this->siteReports, $this->nurseryReports);
        $hasIncomplete = array_reduce($reports, function($hasIncomplete, $report) {
            return $hasIncomplete || !$report->isCompletable();
        });
        if ($hasIncomplete) {
            throw new InvalidStatusException('Task is not submittable due to incomplete reports');
        }

        // Then, ensure all reports are in a complete state. This is broken into two loops to avoid partially
        // submitting an unsubmitable report.
        foreach ($reports as $report) {
            if ($report->isComplete()) {
                continue;
            }

            if ($report->completion == 0) {
                $report->nothingToReport();
            } else {
                $report->awaitingApproval();
            }
        }

        $this->status()->transitionTo(TaskStatusStateMachine::AWAITING_APPROVAL);
    }

    /**
     * If the Task is in any status but DUE or APPROVED, this will update the status of the report based on the status
     * of the reports within it.
     * @throws InvalidStatusException
     */
    public function checkStatus()
    {
        if (in_array($this->status, [TaskStatusStateMachine::DUE, TaskStatusStateMachine::APPROVED])) {
            return;
        }

        // shortcuts that are efficient and don't require looking for update requests
        $reportRelations = [$this->projectReport(), $this->siteReports(), $this->nurseryReports()];
        $reportStatuses = array_unique(array_merge(...array_map(function ($relation) {
            return $relation->distinct()->pluck('status')->all();
        }, $reportRelations)));
        if (count($reportStatuses) == 1 && $reportStatuses[0] == ReportStatusStateMachine::APPROVED) {
            $this->status()->transitionTo(TaskStatusStateMachine::APPROVED);
            return;
        } elseif (
            in_array(ReportStatusStateMachine::DUE, $reportStatuses) ||
            in_array(ReportStatusStateMachine::STARTED, $reportStatuses)
        ) {
            throw new InvalidStatusException('Task has incomplete reports');
        }

        // if we fall through to here, the situation is more complicated and expensive to compute.
        $reportStubs = array_merge(...array_map(function ($relation) {
            // All we need is the status of the report, and the id of the report for getting update requests.
            return $relation
                ->whereIn(
                    'status',
                    [ReportStatusStateMachine::NEEDS_MORE_INFORMATION, ReportStatusStateMachine::AWAITING_APPROVAL])
                ->get('id', 'status');
        }, $reportRelations));

        if (count($reportStubs) == 0) {
            // from the checks above, we know there are reports that are not in approved, or an incomplete state
            // so something is up.
            throw new InvalidStatusException('Task has reports in an invalid state');
        }

        // Separate loops in case there is a report in `awaiting_approval` that has an update request in
        // needs-more-information. In that case, we want to make sure the task goes to needs-more-information
        foreach ($reportStubs as $report) {
            if ($report->status == ReportStatusStateMachine::NEEDS_MORE_INFORMATION) {
                if ($report->updateRequests()->where('status', UpdateRequest::STATUS_AWAITING_APPROVAL)->exists()) {
                    // if a report in needs-more-information has an awaiting-approval update request, ignore it here
                    // because we're specifically looking for a report that's in the needs-more-information state, and
                    // once the update request is in awaiting-approval, it doesn't qualify for task status updating
                    // purposes.
                    continue;
                }

                // A report in needs-more-information causes the task to go to needs-more-information
                $this->status()->transitionTo(TaskStatusStateMachine::NEEDS_MORE_INFORMATION);
                return;
            } elseif ($report->updateRequests()->where('status', UpdateRequest::STATUS_NEEDS_MORE_INFORMATION)->exists()) {
                // an awaiting-approval report with a needs-more-information update request causes the task to go to
                // needs-more-information
                $this->status()->transitionTo(TaskStatusStateMachine::NEEDS_MORE_INFORMATION);
                return;
            }
        }

        // If there are no reports or update requests in needs-more-information, the only option left is that
        // something is in awaiting-approval.
        $this->status()->transitionTo(TaskStatusStateMachine::AWAITING_APPROVAL);
    }

    public function getCompletionStatusAttribute(): string
    {
        if (empty($this->project)) {
            return '';
        }

        $projectCompletion = $this->projectReport()->sum('completion');
        $siteCompletion = $this->siteReports()->sum('completion');
        $nurseryCompletion = $this->nurseryReports()->sum('completion');

        if ($projectCompletion + $siteCompletion + $nurseryCompletion == 0) {
            return 'not-started';
        } else {
            return 'started';
        }
    }
}
