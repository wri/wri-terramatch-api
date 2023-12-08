<?php

namespace App\Models\V2\UpdateRequests;

use App\Models\Framework;
use App\Models\Traits\HasFrameworkKey;
use App\Models\Traits\HasTypes;
use App\Models\Traits\HasUuid;
use App\Models\V2\Organisation;
use App\Models\V2\Projects\Project;
use App\Models\V2\User;
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

    public const STATUS_REQUESTED = 'requested';
    public const STATUS_NO_UPDATE = 'no-update';
    public const STATUS_DRAFT = 'draft';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_AWAITING_APPROVAL = 'awaiting-approval';
    public const STATUS_NEEDS_MORE_INFORMATION = 'needs-more-information';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PUBLISHED = 'published';

    public static $statuses = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_NO_UPDATE => 'No Update',
        self::STATUS_REQUESTED => 'Requested',
        self::STATUS_REJECTED => 'Rejected',
        self::STATUS_AWAITING_APPROVAL => 'Awaiting approval',
        self::STATUS_NEEDS_MORE_INFORMATION => 'Needs more information',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_PUBLISHED => 'Published',
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
}
