<?php

namespace App\Models\Traits;

use App\StateMachines\EntityStatusStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use Illuminate\Support\Str;

/**
 * @property string uuid
 * @property string $status
 * @property string $update_request_status
 * @property string $feedback
 * @property string $feedback_fields
 * @property string $name
 * @property string $readable_status
 * @method status
 */
trait HasEntityStatus
{
    use HasStatus;
    use HasStateMachines;
    use HasEntityStatusScopesAndTransitions;

    public $stateMachines = [
        'status' => EntityStatusStateMachine::class,
    ];

    public static array $statuses = [
        EntityStatusStateMachine::STARTED => 'Started',
        EntityStatusStateMachine::AWAITING_APPROVAL => 'Awaiting approval',
        EntityStatusStateMachine::NEEDS_MORE_INFORMATION => 'Needs more information',
        EntityStatusStateMachine::APPROVED => 'Approved',
    ];

    public function getViewLinkPath(): string
    {
        return '/' . Str::lower(explode_pop('\\', get_class($this))) . '/' . $this->uuid;
    }
}
