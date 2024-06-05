<?php

namespace App\StateMachines;

class SiteStatusStateMachine extends EntityStatusStateMachine
{
    public const DRAFT = 'draft';
    public const PLANTING_IN_PROGRESS = 'planting-in-progress';

    public function transitions(): array
    {
        return [];
    }

    public function defaultState(): ?string
    {
        return self::DRAFT;
    }
}
