<?php

namespace App\StateMachines;

use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;

class TaskStatusStateMachine extends StateMachine
{
    public const DUE = 'due';
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
            self::DUE => [self::AWAITING_APPROVAL],
            self::AWAITING_APPROVAL => [self::NEEDS_MORE_INFORMATION, self::APPROVED],
            self::NEEDS_MORE_INFORMATION => [self::AWAITING_APPROVAL],
        ];
    }

    public function defaultState(): ?string
    {
        return self::DUE;
    }
}
