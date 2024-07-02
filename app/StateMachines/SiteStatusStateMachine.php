<?php

namespace App\StateMachines;

class SiteStatusStateMachine extends EntityStatusStateMachine
{
    public const DRAFT = 'draft';
    public const RESTORATION_IN_PROGRESS = 'restoration-in-progress';

    public function transitions(): array
    {
        return [];
    }

    public function defaultState(): ?string
    {
        return self::DRAFT;
    }
}
