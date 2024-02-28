<?php

namespace App\Models\V2\UpdateRequests;

use App\Models\Framework;
use App\Models\Traits\HasFrameworkKey;
use App\Models\Traits\HasStatus;
use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
use App\StateMachines\UpdateRequestStatusStateMachine;
use Asantibanez\LaravelEloquentStateMachines\Traits\HasStateMachines;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class UpdateRequest extends Model implements ApprovalFlow, AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasTypes;
    use HasFrameworkKey;
    use Auditable;
    use HasStatus;
    use HasStateMachines;

    protected $casts = [
        'published' => 'boolean',
        'content' => 'array',
        'feedback_fields' => 'array',
    ];

    protected $auditInclude = [
        'status',
        'feedback',
        'feedback_fields',
    ];

    public $table = 'v2_update_requests';

    protected $fillable = [
        'organisation_id',
        'project_id',
        'created_by_id',
        'framework_key',
        'updaterequestable_type',
        'updaterequestable_id',
        'status',
        'content',
        'feedback',
        'feedback_fields',
        'old_id',
        'old_model',
    ];

    public const ENTITY_STATUS_NO_UPDATE = 'no-update';

    public static $statuses = [
        UpdateRequestStatusStateMachine::DRAFT => 'Draft',
        UpdateRequestStatusStateMachine::AWAITING_APPROVAL => 'Awaiting approval',
        UpdateRequestStatusStateMachine::NEEDS_MORE_INFORMATION => 'Needs more information',
        UpdateRequestStatusStateMachine::APPROVED => 'Approved',
    ];

    public $stateMachines = [
        'status' => UpdateRequestStatusStateMachine::class,
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /** RELATIONS */
    public function framework(): BelongsTo
    {
        return $this->belongsTo(Framework::class,  'framework_key', 'slug');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id', 'id');
    }

    public function updaterequestable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeIsUnapproved(Builder $query): Builder
    {
        return $query->whereNot('status', UpdateRequestStatusStateMachine::APPROVED);
    }

    public function submitForApproval(): void
    {
        $this->status()->transitionTo(UpdateRequestStatusStateMachine::AWAITING_APPROVAL);
    }

    public function approve($feedback = NULL): void
    {
        if (!is_null($feedback)) {
            $this->feedback = $feedback;
        }
        $this->status()->transitionTo(UpdateRequestStatusStateMachine::APPROVED);
    }

    public function needsMoreInformation($feedback, $feedbackFields): void
    {
        $this->feedback = $feedback;
        $this->feedback_fields = $feedbackFields;
        $this->status()->transitionTo(UpdateRequestStatusStateMachine::NEEDS_MORE_INFORMATION);
    }
}
