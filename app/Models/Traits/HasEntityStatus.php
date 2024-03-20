<?php

namespace App\Models\Traits;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\StateMachines\EntityStatusStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @property string uuid
 * @property string $status
 * @property string $update_request_status
 * @property string $feedback
 * @property string $feedback_fields
 * @property string $name
 * @property string $readable_status
 * @method status
 */
trait HasEntityStatus {
    use HasStatus;
    use HasStateMachines;

    public $stateMachines = [
        'status' => EntityStatusStateMachine::class,
    ];

    public static array $statuses = [
        EntityStatusStateMachine::STARTED => 'Started',
        EntityStatusStateMachine::AWAITING_APPROVAL => 'Awaiting approval',
        EntityStatusStateMachine::NEEDS_MORE_INFORMATION => 'Needs more information',
        EntityStatusStateMachine::APPROVED => 'Approved',
    ];

    public function scopeIsApproved(Builder $query): Builder
    {
        return $this->scopeIsStatus($query, EntityStatusStateMachine::APPROVED);
    }

    public function isEditable(): bool
    {
        return $this->status == EntityStatusStateMachine::STARTED;
    }

    public function approve($feedback): void
    {
        $this->feedback = $feedback;
        $this->feedback_fields = null;

        if ($this->status == EntityStatusStateMachine::APPROVED) {
            // If we were already approved, this may have been called because an update request got approved, and
            // we need to make sure the transition hooks execute, so fake us into awaiting-approval first.
            $this->status = EntityStatusStateMachine::AWAITING_APPROVAL;
        }

        $this->status()->transitionTo(EntityStatusStateMachine::APPROVED);
    }

    public function submitForApproval(): void
    {
        $this->status()->transitionTo(EntityStatusStateMachine::AWAITING_APPROVAL);
    }

    public function needsMoreInformation($feedback, $feedbackFields): void
    {
        $this->feedback = $feedback;
        $this->feedback_fields = $feedbackFields;
        $this->status()->transitionTo(EntityStatusStateMachine::NEEDS_MORE_INFORMATION);
    }

    public function dispatchStatusChangeEvent($user): void
    {
        EntityStatusChangeEvent::dispatch($user, $this, $this->name ?? '', '', $this->readable_status);
    }

    public function getViewLinkPath(): string
    {
        return '/' . Str::lower(explode_pop('\\', get_class($this))) . '/' . $this->uuid;
    }
}