<?php

namespace App\Models\Traits;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\StateMachines\EntityStatusStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property string $status
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
        EntityStatusChangeEvent::dispatch($user, $this, $this->name, '', $this->readable_status);
    }
}