<?php

namespace App\StateMachines;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

class ReportStatusStateMachine extends EntityStatusStateMachine
{
    public const DUE = 'due';

    public function transitions(): array
    {
        return array_merge(
            [
                self::DUE => [self::STARTED, self::AWAITING_APPROVAL],
            ],
            parent::transitions()
        );
    }

    public function defaultState(): ?string
    {
        return self::DUE;
    }

    public function validatorForTransition($from, $to, $model): ?Validator
    {
        if ($from === self::DUE && $to === self::AWAITING_APPROVAL) {
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
        $updateTaskStatus = fn ($fromStatus, $model) => $model->task?->checkStatus();
        return [
            self::NEEDS_MORE_INFORMATION => [$updateTaskStatus],
            self::AWAITING_APPROVAL => [$updateTaskStatus],
            self::APPROVED => [$updateTaskStatus],
        ];
    }
}
