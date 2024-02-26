<?php

namespace App\Models\Traits;

use App\Models\V2\Forms\Form;
use App\StateMachines\ReportStatusStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * @property \Illuminate\Support\Carbon $submitted_at
 * @property string $status
 * @property string $feedback
 * @property string $feedback_fields
 * @property bool $nothing_to_report
 * @property int $completion
 * @property int $created_by
 * @method status
 */
trait HasReportStatus {
    use HasStatus;
    use HasStateMachines;

    private ?Form $form = null;

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

    public function isCompletable(): bool
    {
        if ($this->isComplete()) {
            return true;
        }

        if ($this->status == ReportStatusStateMachine::DUE && !$this->supportsNothingToReport()) {
            return false;
        }

        return $this->status()->canBe(ReportStatusStateMachine::AWAITING_APPROVAL);
    }

    public function isComplete(): bool
    {
        return in_array($this->status, self::COMPLETE_STATUSES);
    }

    public function getForm(): ?Form
    {
        if (is_null($this->form)) {
            $this->form = Form::where('model', get_class($this))
                ->where('framework_key', $this->framework_key)
                ->first();
        }

        return $this->form;
    }

    public function nothingToReport(): void
    {
        $this->nothing_to_report = true;
        $this->awaitingApproval();
    }

    public function updateInProgress(): void
    {
        $this->setCompletion();
        if (empty($report->created_by)) {
            $this->created_by = Auth::user()->id;
        }
        if ($this->status == ReportStatusStateMachine::STARTED) {
            $this->save();
        } else {
            $this->status()->transitionTo(ReportStatusStateMachine::STARTED);
        }
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
        $this->submitted_at = now();
        $this->status()->transitionTo(ReportStatusStateMachine::AWAITING_APPROVAL);
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
}