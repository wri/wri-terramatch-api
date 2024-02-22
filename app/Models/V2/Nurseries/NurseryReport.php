<?php

namespace App\Models\V2\Nurseries;

use App\Http\Resources\V2\NurseryReports\NurseryReportResource;
use App\Http\Resources\V2\NurseryReports\NurseryReportWithSchemaResource;
use App\Models\Framework;
use App\Models\Traits\HasFrameworkKey;
use App\Models\Traits\HasLinkedFields;
use App\Models\Traits\HasReportStatus;
use App\Models\Traits\HasUuid;
use App\Models\Traits\HasV2MediaCollections;
use App\Models\Traits\UsesLinkedFields;
use App\Models\V2\Polygon;
use App\Models\V2\ReportModel;
use App\Models\V2\Tasks\Task;
use App\Models\V2\TreeSpecies\TreeSpecies;
use App\Models\V2\UpdateRequests\ApprovalFlow;
use App\Models\V2\UpdateRequests\UpdateRequest;
use App\Models\V2\User;
use App\Models\V2\Workdays\Workday;
use App\StateMachines\ReportStatusStateMachine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Resources\Json\JsonResource;
use Laravel\Scout\Searchable;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\V2\NurseryReports\AdminStatusNurseryReportControllerTest;

class NurseryReport extends Model implements HasMedia, ApprovalFlow, AuditableContract, ReportModel
{
    use HasFrameworkKey;
    use HasFactory;
    use HasUuid;
    use SoftDeletes;
    use Searchable;
    use HasReportStatus;
    use HasLinkedFields;
    use UsesLinkedFields;
    use InteractsWithMedia;
    use HasV2MediaCollections;
    use Auditable;

    protected $auditInclude = [
        'status',
        'feedback',
        'feedback_fields',
    ];

    public $table = 'v2_nursery_reports';

    public $shortName = 'nursery-report';

    protected $fillable = [
        'framework_key',
        'nursery_id',
        'task_id',
        'due_at',
        'status',
        'update_request_status',
        'submitted_at',
        'nothing_to_report',
        'completion',
        'completion_status',
        'title',
        'seedlings_young_trees',
        'interesting_facts',
        'site_prep',
        'shared_drive_link',
        'created_by',
        'approved_by',
        'old_id',
        'old_model',
        'feedback',
        'feedback_fields',
        'answers',
    ];

    public $casts = [
        'due_at' => 'datetime',
        'submitted_at' => 'datetime',
        'nothing_to_report' => 'boolean',
        'answers' => 'array',
    ];

    public $fileConfiguration = [
        'file' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'other_additional_documents' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'tree_seedling_contributions' => [
            'validation' => 'general-documents',
            'multiple' => true,
        ],
        'photos' => [
            'validation' => 'photos',
            'multiple' => true,
        ],
    ];

    public const COMPLETION_STATUS_NOT_STARTED = 'not-started';
    public const COMPLETION_STATUS_STARTED = 'started';
    public const COMPLETION_STATUS_COMPLETE = 'complete';

    public static $completionStatuses = [
        self::COMPLETION_STATUS_NOT_STARTED => 'Not started',
        self::COMPLETION_STATUS_STARTED => 'Started',
        self::COMPLETION_STATUS_COMPLETE => 'Complete',
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

    /** RELATIONS */
    public function framework(): BelongsTo
    {
        return $this->belongsTo(Framework::class, 'framework_key', 'slug');
    }

    public function updateRequests()
    {
        return $this->morphMany(UpdateRequest::class, 'updaterequestable');
    }

    public function polygons()
    {
        return $this->morphMany(Polygon::class, 'polygonable');
    }

    public function treeSpecies()
    {
        return $this->morphMany(TreeSpecies::class, 'speciesable')->where('collection', 'nursery-seedling');
    }

    public function nursery(): BelongsTo
    {
        return $this->belongsTo(Nursery::class);
    }

    public function project(): BelongsTo
    {
        return  empty($this->nursery) ? $this->nursery : $this->nursery->project();
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function organisation(): BelongsTo
    {
        return empty($this->project) ? $this->project : $this->project->organisation();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workdays()
    {
        return $this->morphMany(Workday::class, 'workdayable');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function toSearchableArray()
    {
        return [
            'project_name' => $this->nursery->project->name,
            'organisation_name' => $this->nursery->project->organisation->name,
        ];
    }

    public function getReportTitleAttribute(): string
    {
        if (empty($this->due_at)) {
            return data_get($this, 'title', '');
        }

        $date = clone $this->due_at->subMonths(1);
        $wEnd = $date->format('F Y');

        $date->subMonths(5);
        $wStart = $date->format('F');

        return "Nursery Report for $wStart - $wEnd";
    }

    public function getTaskUuidAttribute(): ?string
    {
        return $this->task->uuid ?? null;
    }

    public function getFrameworkUuidAttribute(): ?string
    {
        return $this->framework ? $this->framework->uuid : null;
    }

    public function getOrganisationAttribute()
    {
        return $this->nursery ? $this->nursery->organisation : null;
    }

    public function scopeProjectUuid(Builder $query, string $projectUuid): Builder
    {
        return $query->whereHas('nursery', function ($qry) use ($projectUuid) {
            $qry->whereHas('project', function ($qry) use ($projectUuid) {
                $qry->where('uuid', $projectUuid);
            });
        });
    }

    public function scopeNurseryUuid(Builder $query, string $nurseryUuid): Builder
    {
        return $query->whereHas('nursery', function ($qry) use ($nurseryUuid) {
            $qry->where('uuid', $nurseryUuid);
        });
    }

    public function scopeParentId(Builder $query, string $id): Builder
    {
        return $query->where('nursery_id', $id);
    }

    public function scopeCountry(Builder $query, string $country): Builder
    {
        return $query->whereHas('nursery', function ($qry) use ($country) {
            $qry->whereHas('project', function ($qry) use ($country) {
                $qry->where('country', $country);
            });
        });
    }

    public function getReadableCompletionStatusAttribute(): ?string
    {
        if (empty($this->completion_status)) {
            return null;
        }

        return data_get(static::$completionStatuses, $this->completion_status, 'Unknown');
    }

    public function createResource(): JsonResource
    {
        return new NurseryReportResource($this);
    }

    public function createSchemaResource(): JsonResource
    {
        return new NurseryReportWithSchemaResource($this, ['schema' => $this->getForm()]);
    }

    public function supportsNothingToReport(): bool
    {
        return true;
    }

    public function getLinkedFieldsConfig()
    {
        return config('wri.linked-fields.models.nursery-report.fields', []);
    }

    public function getCompletionStatus(): string
    {
        if ($this->completion == 0) {
            return self::COMPLETION_STATUS_NOT_STARTED;
        } elseif ($this->completion == 100) {
            return self::COMPLETION_STATUS_COMPLETE;
        }

        return self::COMPLETION_STATUS_STARTED;
    }
}
