<?php

namespace App\Models\Traits;

use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Database\Eloquent\Builder;

trait HasEntityStatusScopesAndTransitions
{
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
}
