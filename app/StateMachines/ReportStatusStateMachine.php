<?php

namespace App\StateMachines;

use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;

class ReportStatusStateMachine extends StateMachine
{
    public const DUE = 'due';
    public const STARTED = 'started';
    public const NOTHING_TO_REPORT = 'nothing-to-report';
    public const AWAITING_APPROVAL = 'awaiting-approval';
    public const NEEDS_MORE_INFORMATION = 'needs-more-information';
    public const APPROVED = 'approved';

    public function recordHistory(): bool
    {
        return true;
    }

    public function transitions(): array
    {
        // TODO (NJC):
        //   is started -> nothing-to-report valid?

        $states = [
            self::DUE => [self::STARTED, self::NOTHING_TO_REPORT],
            self::STARTED => [self::NOTHING_TO_REPORT, self::AWAITING_APPROVAL],
            self::AWAITING_APPROVAL => [self::APPROVED, self::NEEDS_MORE_INFORMATION],
            self::NEEDS_MORE_INFORMATION => [self::APPROVED],
        ];

        $filterToStates = fn ($toStates) => array_filter($toStates, $this->model->isStatusStateSupported(...));
        return array_map($filterToStates, $states);
    }

    public function defaultState(): ?string
    {
        return self::DUE;
    }
}
