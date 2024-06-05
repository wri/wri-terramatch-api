<?php

namespace App\Models\Traits;

use App\Events\V2\General\EntityStatusChangeEvent;
use App\StateMachines\SiteStatusStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use Illuminate\Support\Str;

trait HasSiteStatus
{
    use HasStatus;
    use HasStateMachines;
    use HasEntityStatusScopesAndTransitions {
        approve as entityStatusApprove;
        submitForApproval as entityStatusSubmitForApproval;
    }

    public $stateMachines = [
        'status' => SiteStatusStateMachine::class,
    ];

    public static array $statuses = [
        SiteStatusStateMachine::DRAFT => 'Draft',
        SiteStatusStateMachine::AWAITING_APPROVAL => 'Awaiting approval',
        SiteStatusStateMachine::NEEDS_MORE_INFORMATION => 'Needs more information',
        SiteStatusStateMachine::PLANTING_IN_PROGRESS => 'Planting in progress',
        SiteStatusStateMachine::APPROVED => 'Approved',
    ];

    public function dispatchStatusChangeEvent($user): void
    {
        EntityStatusChangeEvent::dispatch($user, $this, $this->name ?? '', '', $this->readable_status);
    }

    public function getViewLinkPath(): string
    {
        return '/' . Str::lower(explode_pop('\\', get_class($this))) . '/' . $this->uuid;
    }
}
