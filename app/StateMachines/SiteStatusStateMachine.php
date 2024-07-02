<?php

namespace App\StateMachines;

class SiteStatusStateMachine extends EntityStatusStateMachine
{
    public const DRAFT = 'draft';
    public const AWAITING_APPROVAL = 'awaiting-approval';
    public const APPROVED = 'approved';
    public const NEEDS_MORE_INFORMATION = 'needs-more-information';
    public const RESTORATION_IN_PROGRESS = 'restoration-in-progress';

    public function recordHistory(): bool
    {
        return true;
    }

    public function transitions(): array
    {
        $parentTransitions = parent::transitions();

        return array_merge(
            $parentTransitions,
            [
                parent::APPROVED => [self::RESTORATION_IN_PROGRESS],
            ]
        );
    }

    public function defaultState(): ?string
    {
        return self::DRAFT;
    }
}
