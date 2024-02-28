<?php

namespace App\StateMachines;

use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;

class EntityStatusStateMachine extends StateMachine
{
    public const STARTED = 'started';
    public const AWAITING_APPROVAL = 'awaiting-approval';
    public const APPROVED = 'approved';
    public const NEEDS_MORE_INFORMATION = 'needs-more-information';

    public function recordHistory(): bool
    {
        return true;
    }

    public function transitions(): array
    {
        return [
            self::STARTED => [self::AWAITING_APPROVAL],
            self::AWAITING_APPROVAL => [self::APPROVED, self::NEEDS_MORE_INFORMATION],
            self::NEEDS_MORE_INFORMATION => [self::APPROVED],
            self::APPROVED => [self::AWAITING_APPROVAL],
        ];
    }

    public function defaultState(): ?string
    {
        return self::STARTED;
    }
}
