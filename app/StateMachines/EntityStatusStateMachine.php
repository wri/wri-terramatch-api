<?php

namespace App\StateMachines;

use App\Jobs\V2\SendEntityStatusChangeEmailsJob;
use Asantibanez\LaravelEloquentStateMachines\StateMachines\StateMachine;

class EntityStatusStateMachine extends StateMachine
{
    public const STARTED = 'started';
    public const AWAITING_APPROVAL = 'awaiting-approval';
    public const APPROVED = 'approved';
    public const NEEDS_MORE_INFORMATION = 'needs-more-information';

    public function recordHistory(): bool
    {
        // We had this turned on for a long time and never made use of it; time to stop recording transition history.
        return false;
    }

    public function transitions(): array
    {
        return [
            self::STARTED => [self::AWAITING_APPROVAL],
            self::AWAITING_APPROVAL => [self::APPROVED, self::NEEDS_MORE_INFORMATION],
            self::NEEDS_MORE_INFORMATION => [self::APPROVED, self::AWAITING_APPROVAL],
            self::APPROVED => [self::NEEDS_MORE_INFORMATION],
        ];
    }

    public function defaultState(): ?string
    {
        return self::STARTED;
    }

    public function afterTransitionHooks(): array
    {
        $hooks = parent::afterTransitionHooks();

        $sendStatusChangeEmail = fn ($fromStatus, $model) => SendEntityStatusChangeEmailsJob::dispatch($model);
        $hooks[self::NEEDS_MORE_INFORMATION][] = $sendStatusChangeEmail;
        $hooks[self::APPROVED][] = $sendStatusChangeEmail;

        return $hooks;
    }
}
