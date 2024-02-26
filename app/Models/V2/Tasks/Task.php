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

        $this->status()->awaitingApproval();
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
