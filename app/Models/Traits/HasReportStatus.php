<?php

namespace App\Models\Traits;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\StateMachines\ReportStatusStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property string $uuid
 * @property string $status
 * @property string $update_request_status
 * @property string $feedback
 * @property string $feedback_fields
 * @property bool $nothing_to_report
 * @property int $completion
 * @property int $created_by
 * @method status
 */
trait HasReportStatus
{
    use HasStatus;
    use HasStateMachines;
    use HasEntityStatusScopesAndTransitions {
        approve as entityStatusApprove;
        submitForApproval as entityStatusSubmitForApproval;
    }

    public $stateMachines = [
        'status' => ReportStatusStateMachine::class,
    ];

    public function supportsNothingToReport(): bool
    {
        // Reports must opt in to "nothing to report" functionality.
        return false;
    }

    public static array $statuses = [
        ReportStatusStateMachine::DUE => 'Due',
        ReportStatusStateMachine::STARTED => 'Started',
        ReportStatusStateMachine::AWAITING_APPROVAL => 'Awaiting approval',
        ReportStatusStateMachine::NEEDS_MORE_INFORMATION => 'Needs more information',
        ReportStatusStateMachine::APPROVED => 'Approved',
    ];

    public const UNSUBMITTED_STATUSES = [
        ReportStatusStateMachine::DUE,
        ReportStatusStateMachine::STARTED,
    ];

    public const COMPLETE_STATUSES = [
        ReportStatusStateMachine::AWAITING_APPROVAL,
        ReportStatusStateMachine::APPROVED,
    ];

    public function scopeIsIncomplete(Builder $query): Builder
    {
        return $query->whereNotIn('status', self::COMPLETE_STATUSES);
    }

    public function scopeIsComplete(Builder $query): Builder
    {
        return $query->whereIn('status', self::COMPLETE_STATUSES);
    }

    public function scopeHasBeenSubmitted(Builder $query): Builder
    {
        return $query->whereNotIn('status', self::UNSUBMITTED_STATUSES);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [ReportStatusStateMachine::DUE, ReportStatusStateMachine::STARTED]) ||
            ($this->nothing_to_report && $this->status == ReportStatusStateMachine::AWAITING_APPROVAL);
    }

    public function isCompletable(): bool
    {
        if ($this->hasCompleteStatus()) {
            return true;
        }

        if ($this->status == ReportStatusStateMachine::DUE && ! $this->supportsNothingToReport()) {
            return false;
        }

        return $this->status()->canBe(ReportStatusStateMachine::AWAITING_APPROVAL);
    }

    public function hasCompleteStatus(): bool
    {
        return in_array($this->status, self::COMPLETE_STATUSES);
    }

    public function nothingToReport(): void
    {
        if (! $this->supportsNothingToReport()) {
            throw new \InvalidArgumentException(
                'This report model does not support the nothing-to-report status'
            );
        }

        $this->nothing_to_report = true;
        $this->submitForApproval();
    }

    public function updateInProgress(bool $isAdmin = false): void
    {
        $this->setCompletion();
        if (! $isAdmin && empty($report->created_by)) {
            $this->created_by = Auth::user()->id;
        }

        // An admin should be able to directly update a report without a transition unless it's in `due`, in which case
        // we want the transition to go ahead and take place.
        $adminDirectSave = $isAdmin && $this->status != ReportStatusStateMachine::DUE;
        if ($this->status == ReportStatusStateMachine::STARTED || $adminDirectSave) {
            $this->save();
        } else {
            $this->status()->transitionTo(ReportStatusStateMachine::STARTED);
        }

        if ($this->supportsNothingToReport() && $this->nothing_to_report) {
            // This update has to happen after the transition above, or the transition validation will fail
            // (see ReportStatusStateMachine)
            $this->update(['nothing_to_report' => false]);
        }
    }

    public function approve($feedback): void
    {
        $this->setCompletion();
        $this->entityStatusApprove($feedback);
        $this->update(['nothing_to_report' => false]);
    }

    public function submitForApproval(): void
    {
        if (empty($this->submitted_at)) {
            $this->completion = 100;
            $this->submitted_at = now();
        }
        $this->entityStatusSubmitForApproval();
    }

    public function setCompletion(): void
    {
        $this->completion = $this->calculateCompletion($this->getForm());
    }

    public function getReadableCompletionStatusAttribute(): ?string
    {
        return match ($this->completion) {
            0 => 'Not Started',
            100 => 'Complete',
            default => 'Started'
        };
    }

    public function dispatchStatusChangeEvent($user): void
    {
        EntityStatusChangeEvent::dispatch($user, $this);
    }

    public function getViewLinkPath(): string
    {
        return '/reports/' . Str::kebab(explode_pop('\\', get_class($this))) . '/' . $this->uuid;
    }
}
