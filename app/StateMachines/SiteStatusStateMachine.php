<?php

namespace App\StateMachines;

class SiteStatusStateMachine extends EntityStatusStateMachine
{
    public const DRAFT = 'draft';
    public const AWAITING_APPROVAL = 'awaiting-approval';
    public const APPROVED = 'approved';
    public const NEEDS_MORE_INFORMATION = 'needs-more-information';
    public const RESTORATION_IN_PROGRESS = 'restoration-in-progress';

    public function transitions(): array
    {
        return [
                self::DRAFT => [self::AWAITING_APPROVAL],
                self::AWAITING_APPROVAL => [self::APPROVED, self::NEEDS_MORE_INFORMATION],
                self::NEEDS_MORE_INFORMATION => [self::APPROVED, self::RESTORATION_IN_PROGRESS],
                self::RESTORATION_IN_PROGRESS => [self::NEEDS_MORE_INFORMATION, self::APPROVED],
                self::APPROVED => [self::NEEDS_MORE_INFORMATION, self::RESTORATION_IN_PROGRESS],
        ];
    }

    public function defaultState(): ?string
    {
        return self::DRAFT;
    }
}
