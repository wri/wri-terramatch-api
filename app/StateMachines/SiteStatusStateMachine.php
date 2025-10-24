<?php

namespace App\StateMachines;

class SiteStatusStateMachine extends EntityStatusStateMachine
{
    public function transitions(): array
    {
        $parentTransitions = parent::transitions();

        return $parentTransitions;
    }
}
