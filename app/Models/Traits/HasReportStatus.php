<?php

namespace App\Models\Traits;

use App\Models\V2\Forms\Form;
use App\StateMachines\ReportStatusStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property string $completion_status
 * @property string $status
 * @property string $feedback
 * @property string $feedback_fields
 * @property bool $nothing_to_report
 * @property int $completion
 * @property int $created_by
 * @method status
 * @method getCompletionStatus
 */
trait HasReportStatus {
    use HasStatus;
    use HasStateMachines;

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

    public const COMPLETE_STATUSES = [
        ReportStatusStateMachine::AWAITING_APPROVAL,
        ReportStatusStateMachine::APPROVED,
    ];

    public function scopeIsApproved(Builder $query): Builder
    {
        return $this->scopeIsStatus($query, ReportStatusStateMachine::APPROVED);
    }

    public function scopeIsIncomplete(Builder $query): Builder
    {
        return $query->whereNotIn('status', self::COMPLETE_STATUSES);
    }

    public function scopeIsComplete(Builder $query): Builder
    {
        return $query->whereIn('status', self::COMPLETE_STATUSES);
    }

    public function isEditable(): bool
    {
        return in_array($this->status, [ReportStatusStateMachine::DUE, ReportStatusStateMachine::STARTED]);
    }

    public function getForm(): ?Form
    {
        return Form::where('model', get_class($this))
            ->where('framework_key', $this->framework_key)
            ->first();
    }

    public function nothingToReport(): void
    {
        $this->nothing_to_report = true;
        $this->awaitingApproval();
    }

    public function approve($feedback = NULL): void
    {
        $this->setCompletion();
        if (!is_null($feedback)) {
            $this->feedback = $feedback;
        }
        $this->status()->transitionTo(ReportStatusStateMachine::APPROVED);
    }

    public function needsMoreInformation($feedback, $feedbackFields): void
    {
        $this->feedback = $feedback;
        $this->feedback_fields = $feedbackFields;
        $this->status()->transitionTo(ReportStatusStateMachine::NEEDS_MORE_INFORMATION);
    }

    public function awaitingApproval(): void
    {
        $this->completion = 100;
        // TODO (NJC) this should be going away in a future commit in TM-561
        $this->completion_status = 'complete';
        $this->submitted_at = now();
        $this->status()->transitionTo(ReportStatusStateMachine::AWAITING_APPROVAL);
    }

    public function setCompletion(): void
    {
        $this->completion = $this->calculateCompletion($this->getForm());
        // TODO (NJC) this should be going away in a future commit in TM-561
        $this->completion_status = $this->getCompletionStatus();
    }
}