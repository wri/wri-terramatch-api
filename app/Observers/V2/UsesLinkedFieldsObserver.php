<?php

namespace App\Observers\V2;

use App\Models\Traits\UsesLinkedFields;
use App\StateMachines\EntityStatusStateMachine;
use Illuminate\Database\Eloquent\Model;

class UsesLinkedFieldsObserver
{
    /**
     * Handle the model "retrieved" event.
     * Clean conditional answers when entity is approved.
     */
    public function retrieved(Model $model): void
    {
        // Check if model uses UsesLinkedFields trait
        if (! $this->usesLinkedFieldsTrait($model)) {
            return;
        }

        // Only clean if entity is approved
        if ($this->isEntityApproved($model)) {
            $form = $model->getForm();
            if ($form && method_exists($model, 'cleanConditionalAnswers')) {
                $model->cleanConditionalAnswers($form);
            }
        }
    }

    /**
     * Check if the model uses the UsesLinkedFields trait
     */
    private function usesLinkedFieldsTrait(Model $model): bool
    {
        $traits = class_uses_recursive(get_class($model));

        return in_array(UsesLinkedFields::class, $traits);
    }

    /**
     * Check if the model's status is approved
     */
    private function isEntityApproved(Model $model): bool
    {
        if (! isset($model->status)) {
            return false;
        }

        return $model->status === EntityStatusStateMachine::APPROVED;
    }
}
