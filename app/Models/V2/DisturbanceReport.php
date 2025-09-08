<?php

namespace App\Models\V2;

use App\Models\Traits\HasEntityResources;
use App\Models\Traits\HasReportStatus;
use App\Models\Traits\HasUpdateRequests;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use App\Models\V2\Forms\Form;
use App\Models\V2\Projects\Project;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Znck\Eloquent\Traits\BelongsToThrough as BelongsToThroughTrait;

class DisturbanceReport extends Model implements MediaModel, ReportModel, AuditableContract
{
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use HasReportStatus;
    use HasUpdateRequests;
    use UsesLinkedFields;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use HasEntityResources;
    use Auditable;
    use BelongsToThroughTrait;

    protected $table = 'disturbance_reports';

    protected $fillable = [
        'status',
        'date_of_incident',
        'intensity',
        'title',
        'disturbance_type',
        'disturbance_subtype',
        'extent',
        'people_affected',
        'property_affected',
        'date_of_disturbance',
        'monetary_damage',
        'description',
        'action_description',
        'due_at',
        'project_id',
        'framework_key',
        'update_request_status',
        'submitted_at',
        'feedback',
        'feedback_fields',
        'answers',
    ];

    protected $casts = [
        'due_at' => 'datetime',
        'submitted_at' => 'datetime',
        'date_of_incident' => 'date',
        'date_of_disturbance' => 'date',
        'property_affected' => 'array',
        'monetary_damage' => 'float',
        'nothing_to_report' => 'boolean',
        'answers' => 'array',
        'disturbance_subtype' => 'array',
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

    public const DISTURBANCE_FORM_TYPE = 'disturbance-report';

    public $shortName = 'disturbance-report';

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
        return "Disturbance Report #{$this->id}";
    }

    public function getParentNameAttribute(): string
    {
        return $this->project?->name ?? '';
    }

    public function getForm(): Form
    {
        $form = Form::where('type', self::DISTURBANCE_FORM_TYPE)
            ->first();

        if (! $form) {
            throw new \RuntimeException('No form found for DisturbanceReport without a form type');
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
}
