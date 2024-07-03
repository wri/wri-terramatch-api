<?php

namespace App\StateMachines;

class SiteStatusStateMachine extends EntityStatusStateMachine
{
    public const AWAITING_APPROVAL = 'awaiting-approval';
    public const APPROVED = 'approved';
    public const NEEDS_MORE_INFORMATION = 'needs-more-information';
    public const RESTORATION_IN_PROGRESS = 'restoration-in-progress';

    public function transitions(): array
    {
        $parentTransitions = parent::transitions();

        return [
            $parentTransitions,
            parent::AWAITING_APPROVAL => [self::RESTORATION_IN_PROGRESS, parent::NEEDS_MORE_INFORMATION],
            parent::NEEDS_MORE_INFORMATION => [self::RESTORATION_IN_PROGRESS, parent::AWAITING_APPROVAL],
            self::RESTORATION_IN_PROGRESS => [parent::NEEDS_MORE_INFORMATION, parent::APPROVED],
            self::APPROVED => [self::RESTORATION_IN_PROGRESS, self::AWAITING_APPROVAL],
        ];
    }

    public function defaultState(): ?string
    {
        return parent::STARTED;
    }
}
