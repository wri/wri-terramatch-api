<?php

namespace App\StateMachines;

class SiteStatusStateMachine extends EntityStatusStateMachine
{
    public const RESTORATION_IN_PROGRESS = 'restoration-in-progress';

    public function transitions(): array
    {
        $parentTransitions = parent::transitions();

        $parentTransitions[self::NEEDS_MORE_INFORMATION][] = self::RESTORATION_IN_PROGRESS;
        $parentTransitions[self::APPROVED][] = self::RESTORATION_IN_PROGRESS;

        return array_merge(
            [
                self::RESTORATION_IN_PROGRESS => [self::NEEDS_MORE_INFORMATION, self::APPROVED],
            ],
            $parentTransitions
        );
    }
}
