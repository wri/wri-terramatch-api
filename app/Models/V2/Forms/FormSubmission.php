<?php

namespace App\Models\V2\Forms;

use App\Models\Traits\HasStatus;
use App\Models\Traits\HasUuid;
use App\Models\Traits\UsesLinkedFields;
use App\Models\V2\Forms\Casts\AnswersCast;
use App\Models\V2\Organisation;
use App\Models\V2\ProjectPitch;
use App\Models\V2\Stages\Stage;
use App\Models\V2\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class FormSubmission extends Model implements AuditableContract
{
    use HasFactory;
    use SoftDeletes;
    use HasUuid;
    use HasStatus;
    use UsesLinkedFields;
    use Auditable;

    public const STATUS_STARTED = 'started';
    public const STATUS_AWAITING_APPROVAL = 'awaiting-approval';
    public const STATUS_REQUIRES_MORE_INFORMATION = 'requires-more-information';
    public const STATUS_SUBMITTED_REQUIRES_MORE_INFORMATION = 'submitted-requires-more-information';
    public const STATUS_STAGE_CHANGE = 'stage-change';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public static $statuses = [
        self::STATUS_STARTED => 'Started',
        self::STATUS_AWAITING_APPROVAL => 'Awaiting Approval',
        self::STATUS_REQUIRES_MORE_INFORMATION => 'Requires More Information',
        self::STATUS_SUBMITTED_REQUIRES_MORE_INFORMATION => 'Submitted Requires More Information',
        self::STATUS_STAGE_CHANGE => 'Stage Change',
        self::STATUS_APPROVED => 'Approved',
        self::STATUS_REJECTED => 'Rejected',
    ];

    public static $userControlledStatuses = [
        self::STATUS_STARTED => 'Started',
        self::STATUS_AWAITING_APPROVAL => 'Awaiting Approval',
        self::STATUS_SUBMITTED_REQUIRES_MORE_INFORMATION => 'Submitted Requires More Information',
    ];

    protected $auditInclude = [
        'status',
        'feedback',
        'feedback_fields',
    ];

    protected $fillable = [
        'form_id',
        'stage_uuid',
        'user_id',
        'organisation_uuid',
        'project_pitch_uuid',
        'application_id',
        'answers',
        'status',
        'name',
        'feedback',
        'feedback_fields',
    ];

    protected $casts = [
        'answers' => 'json', //AnswersCast::class,
        'feedback_fields' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class, 'form_id', 'uuid');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class, 'stage_uuid', 'uuid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'uuid');
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'organisation_uuid', 'uuid');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id', 'id');
    }

    public function projectPitch(): BelongsTo
    {
        return $this->belongsTo(ProjectPitch::class, 'project_pitch_uuid', 'uuid');
    }

    public function targetable()
    {
        return $this->morphTo();
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'answers' => $this->answers,
        ];
    }

    public static function search($query)
    {
        return self::select('form_submissions.*')
            ->where('form_submissions.name', 'like', "%$query%");
    }

    public function scopeFundingProgrammeUuid(Builder $query, string $fundingProgrammeId): Builder
    {
        return $query->where('stages.funding_programme_id', '=', $fundingProgrammeId)
            ->join('forms', 'form_submissions.form_id', '=', 'forms.uuid')
            ->join('stages', 'forms.stage_id', '=', 'stages.uuid');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}
