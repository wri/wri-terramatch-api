<?php

namespace App\StateMachines;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

class ReportStatusStateMachine extends EntityStatusStateMachine
{
    public const DUE = 'due';

    public function transitions(): array
    {
        $parentTransitions = parent::transitions();
        // Reports can go from awaiting approval to started if the nothing_to_report flag is true (see validations below)
        $parentTransitions[self::AWAITING_APPROVAL][] = self::STARTED;
        return array_merge(
            [
                self::DUE => [self::STARTED, self::AWAITING_APPROVAL],
            ],
            $parentTransitions
        );
    }

    public function defaultState(): ?string
    {
        return self::DUE;
    }

    public function validatorForTransition($from, $to, $model): ?Validator
    {
        if (
            ($from === self::DUE && $to === self::AWAITING_APPROVAL) ||
            ($from === self::AWAITING_APPROVAL && $to === self::STARTED)
        ) {
            return ValidatorFacade::make([
                'nothing_to_report' => $model->nothing_to_report,
            ], [
                'nothing_to_report' => 'accepted',
            ]);
        }

        return parent::validatorForTransition($from, $to, $model);
    }

    public function afterTransitionHooks(): array
    {
        $hooks = parent::afterTransitionHooks();

        $updateTaskStatus = fn ($fromStatus, $model) => $model->task?->checkStatus();
        $hooks[self::NEEDS_MORE_INFORMATION][] = $updateTaskStatus;
        $hooks[self::AWAITING_APPROVAL][] = $updateTaskStatus;
        $hooks[self::APPROVED][] = $updateTaskStatus;

        return $hooks;
    }

    private function addHook ($hooks, $status, $hook)
    {
        $hooks[$status] = [$hook];
    }
}
