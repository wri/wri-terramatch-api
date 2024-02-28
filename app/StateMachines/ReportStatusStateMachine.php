<?php

namespace App\StateMachines;

use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;

class ReportStatusStateMachine extends StateMachine
{
    public const DUE = 'due';
    public const STARTED = 'started';
    public const AWAITING_APPROVAL = 'awaiting-approval';
    public const NEEDS_MORE_INFORMATION = 'needs-more-information';
    public const APPROVED = 'approved';

    public function recordHistory(): bool
    {
        return true;
    }

    public function transitions(): array
    {
        return [
            self::DUE => [self::STARTED, self::AWAITING_APPROVAL],
            self::STARTED => [self::AWAITING_APPROVAL],
            self::AWAITING_APPROVAL => [self::APPROVED, self::NEEDS_MORE_INFORMATION],
            self::NEEDS_MORE_INFORMATION => [self::APPROVED],
        ];
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
        $updateTaskStatus = fn ($fromStatus, $model) => $model->task->checkStatus();
        return [
            self::NEEDS_MORE_INFORMATION => [$updateTaskStatus],
            self::AWAITING_APPROVAL => [$updateTaskStatus],
            self::APPROVED => [$updateTaskStatus],
        ];
    }
}
