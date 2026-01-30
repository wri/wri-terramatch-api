<?php

namespace App\Models\V2;

use App\Models\Traits\HasDemographics;
use App\Models\Traits\HasReportStatus;
use App\Models\Traits\HasUpdateRequests;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use App\Models\V2\AuditStatus\AuditStatus;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use App\Models\V2\Tasks\Task;
use App\Models\V2\Trackings\DemographicCollections;
use App\Models\V2\Trackings\Tracking;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

class SrpReport extends Model implements MediaModel, ReportModel, AuditableContract, AuditableModel
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use HasReportStatus;
    use UsesLinkedFields;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use Auditable;
    use HasUpdateRequests;
    use BelongsToThroughTrait;
    use HasDemographics;

    protected $table = 'srp_reports';

    protected $fillable = [
        'status',
        'title',
        'restoration_partners_description',
        'total_unique_restoration_partners',
        'due_at',
        'year',
        'project_id',
        'framework_key',
        'update_request_status',
        'submitted_at',
        'feedback',
        'feedback_fields',
        'answers',
        'task_id',
        'completion',
        // virtual (see HasDemographics trait)
        'other_workdays_description',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'submitted_at' => 'datetime',
        'year' => 'integer',
        'nothing_to_report' => 'boolean',
        'answers' => 'array',
    ];

    protected $auditInclude = [
        'status',
        'feedback',
        'feedback_fields',
    ];

    public $fileConfiguration = [
        'media' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
    ];

    public const SRP_FORM_TYPE = 'srp-report';

    public $shortName = 'srp-report';

    // Required by the HasDemographics trait.
    public const DEMOGRAPHIC_COLLECTIONS = [
        Tracking::WORKDAY_TYPE => [
            'paid' => [
                DemographicCollections::PAID_NURSERY_OPERATIONS,
                DemographicCollections::PAID_PROJECT_MANAGEMENT,
                DemographicCollections::PAID_OTHER,
            ],
            'volunteer' => [
                DemographicCollections::VOLUNTEER_NURSERY_OPERATIONS,
                DemographicCollections::VOLUNTEER_PROJECT_MANAGEMENT,
                DemographicCollections::VOLUNTEER_OTHER,
            ],
            'other' => [
                DemographicCollections::PAID_OTHER,
                DemographicCollections::VOLUNTEER_OTHER,
            ],
            'finance' => [
                DemographicCollections::DIRECT,
                DemographicCollections::CONVERGENCE,
            ],
            'direct' => [
                DemographicCollections::DIRECT,
            ],
            'convergence' => [
                DemographicCollections::CONVERGENCE,
            ],
        ],
        Tracking::RESTORATION_PARTNER_TYPE => [
            'direct' => [
                DemographicCollections::DIRECT_INCOME,
                DemographicCollections::DIRECT_BENEFITS,
                DemographicCollections::DIRECT_CONSERVATION_PAYMENTS,
                DemographicCollections::DIRECT_MARKET_ACCESS,
                DemographicCollections::DIRECT_CAPACITY,
                DemographicCollections::DIRECT_TRAINING,
                DemographicCollections::DIRECT_LAND_TITLE,
                DemographicCollections::DIRECT_LIVELIHOODS,
                DemographicCollections::DIRECT_PRODUCTIVITY,
                DemographicCollections::DIRECT_OTHER,
            ],
            'indirect' => [
                DemographicCollections::INDIRECT_INCOME,
                DemographicCollections::INDIRECT_BENEFITS,
                DemographicCollections::INDIRECT_CONSERVATION_PAYMENTS,
                DemographicCollections::INDIRECT_MARKET_ACCESS,
                DemographicCollections::INDIRECT_CAPACITY,
                DemographicCollections::INDIRECT_TRAINING,
                DemographicCollections::INDIRECT_LAND_TITLE,
                DemographicCollections::INDIRECT_LIVELIHOODS,
                DemographicCollections::INDIRECT_PRODUCTIVITY,
                DemographicCollections::INDIRECT_OTHER,
            ],
            'other' => [
                DemographicCollections::DIRECT_OTHER,
                DemographicCollections::INDIRECT_OTHER,
            ],
        ],
        Tracking::JOBS_TYPE => [
            'full-time' => [
                DemographicCollections::FULL_TIME,
                DemographicCollections::FULL_TIME_CLT,
            ],
            'part-time' => [
                DemographicCollections::PART_TIME,
                DemographicCollections::PART_TIME_CLT,
            ],
        ],
        Tracking::VOLUNTEERS_TYPE => DemographicCollections::VOLUNTEER,
        Tracking::ALL_BENEFICIARIES_TYPE => DemographicCollections::ALL,
        Tracking::TRAINING_BENEFICIARIES_TYPE => DemographicCollections::TRAINING,
        Tracking::ASSOCIATES_TYPE => DemographicCollections::ALL,
    ];

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumbnail')
            ->width(350)
            ->height(211)
            ->nonQueued();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getAuditableNameAttribute(): string
    {
        return "Annual Socio Economic Restoration Report #{$this->id}";
    }

    public function getParentNameAttribute(): string
    {
        return $this->project?->name ?? '';
    }

    public function getForm(): Form
    {
        $form = Form::where('type', self::SRP_FORM_TYPE)
            ->first();

        if (! $form) {
            throw new \RuntimeException('No form found for AnnualSocioEconomicRestorationReport without a form type');
        }

        return $form;
    }

    public function supportsNothingToReport(): bool
    {
        return true;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function organisation(): BelongsToThrough
    {
        return $this->belongsToThrough(
            Organisation::class,
            Project::class,
            foreignKeyLookup: [Project::class => 'project_id']
        );
    }

    public function parentEntity(): BelongsTo
    {
        return $this->project();
    }

    public function auditStatuses(): MorphMany
    {
        return $this->morphMany(AuditStatus::class, 'auditable');
    }
}
